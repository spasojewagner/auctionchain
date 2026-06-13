<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * EscrowService - centralna logika za rad sa novcem u sistemu.
 *
 * Svaka operacija sa novcem mora da prolazi kroz ovaj servis jer:
 * 1. Obezbedjuje atomicnost (DB::transaction)
 * 2. Beleži svaku promenu u transactions tabeli (audit trail)
 * 3. Atomarno zakljucava red u users tabeli (lockForUpdate) da spreci
 *    race condition kad dva korisnika istovremeno licitiraju na istu aukciju.
 */
class EscrowService
{
    /**
     * Postavlja novu ponudu na aukciju.
     * - Validira: korisnik nije prodavac, aukcija aktivna, iznos > trenutne cene, ima dovoljno balansa
     * - Vraca prethodnom najvisem ponudjacu njegov locked_balance
     * - Zakljucava (locked_balance) sredstva novog najviseg ponudjaca
     * - Azurira current_price aukcije
     * - Salje notifikaciju nadlicitiranom korisniku
     */
    public function placeBid(User $bidder, Auction $auction, float $amount): Bid
    {
        return DB::transaction(function () use ($bidder, $auction, $amount) {
            // Reload sa lock-om - sprecava race conditions
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);
            $bidder = User::lockForUpdate()->findOrFail($bidder->id);

            if (!$auction->isActive()) {
                throw new RuntimeException('Aukcija nije aktivna.');
            }

            if ($auction->seller_id === $bidder->id) {
                throw new InvalidArgumentException('Ne možete licitirati na sopstvenu aukciju.');
            }

            if ($bidder->is_suspended) {
                throw new RuntimeException('Vaš nalog je suspendovan.');
            }

            $currentHighest = (float) $auction->current_price;
            $hasBids = $auction->bids()->exists();

            // Ako vec ima ponuda, mora biti veca; ako nema, mora biti >= starting_price
            $minimumBid = $hasBids ? $currentHighest + 0.01 : (float) $auction->starting_price;

            if ($amount < $minimumBid) {
                throw new InvalidArgumentException("Ponuda mora biti najmanje " . number_format($minimumBid, 2) . " RSD.");
            }

            // Provera da li bidder vec ima najvisu ponudu - ne moze sam sebe da nadlicitira
            $previousHighest = $auction->bids()->orderByDesc('amount')->first();
            if ($previousHighest && $previousHighest->user_id === $bidder->id) {
                throw new InvalidArgumentException('Već imate najvišu ponudu na ovoj aukciji.');
            }

            // Provera dostupnog balansa
            if ((float) $bidder->balance < $amount) {
                throw new RuntimeException('Nemate dovoljno sredstava na balansu. Potrebno: ' . number_format($amount, 2) . ' RSD, dostupno: ' . number_format($bidder->balance, 2) . ' RSD.');
            }

            // 1. Vrati prethodnom najvisem ponudjacu njegov locked_balance
            if ($previousHighest) {
                $this->unlockFundsForUser(
                    $previousHighest->user_id,
                    (float) $previousHighest->amount,
                    $auction->id,
                    'Nadlicitirani ste na aukciji #' . $auction->id
                );

                // Notifikuj nadlicitiranog
                Notification::create([
                    'user_id' => $previousHighest->user_id,
                    'auction_id' => $auction->id,
                    'type' => 'outbid',
                    'message' => "Nadlicitirani ste na aukciji '{$auction->title}'.",
                ]);
            }

            // 2. Zakljucaj sredstva novog ponudjaca
            $bidder->balance = (float) $bidder->balance - $amount;
            $bidder->locked_balance = (float) $bidder->locked_balance + $amount;
            $bidder->save();

            Transaction::create([
                'user_id' => $bidder->id,
                'auction_id' => $auction->id,
                'type' => 'lock',
                'amount' => $amount,
                'balance_after' => $bidder->balance,
                'locked_balance_after' => $bidder->locked_balance,
                'note' => 'Licitacija na aukciji #' . $auction->id,
            ]);

            // 3. Kreiraj bid i azuriraj current_price aukcije
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $bidder->id,
                'amount' => $amount,
            ]);

            $auction->current_price = $amount;
            $auction->save();

            return $bid;
        });
    }

    /**
     * Zavrsava aukciju koja je istekla.
     * - Postavlja status na 'ended'
     * - Postavlja winner_id na korisnika sa najvisom ponudom
     * - Salje notifikacije pobedniku i prodavcu
     * - Ako nema ponuda, postavlja status na 'cancelled'
     */
    public function endAuction(Auction $auction): void
    {
        DB::transaction(function () use ($auction) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if ($auction->status !== 'active') {
                return; // Vec zavrsena
            }

            $winningBid = $auction->bids()->orderByDesc('amount')->first();

            if (!$winningBid) {
                $auction->status = 'cancelled';
                $auction->save();
                return;
            }

            $auction->status = 'ended';
            $auction->winner_id = $winningBid->user_id;
            $auction->save();

            // Notifikacije
            Notification::create([
                'user_id' => $winningBid->user_id,
                'auction_id' => $auction->id,
                'type' => 'auction_won',
                'message' => "Pobedili ste na aukciji '{$auction->title}'! Iznos: " . number_format($winningBid->amount, 2) . " RSD.",
            ]);

            Notification::create([
                'user_id' => $auction->seller_id,
                'auction_id' => $auction->id,
                'type' => 'auction_ended_seller',
                'message' => "Vaša aukcija '{$auction->title}' je završena. Pobednik je platio " . number_format($winningBid->amount, 2) . " RSD.",
            ]);
        });
    }

    /**
     * Kupac potvrdjuje prijem - novac se oslobadja prodavcu.
     */
    public function confirmDelivery(Auction $auction, User $buyer): void
    {
        DB::transaction(function () use ($auction, $buyer) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if ($auction->status !== 'ended') {
                throw new RuntimeException('Aukcija nije u stanju da se potvrdi prijem.');
            }

            if ($auction->winner_id !== $buyer->id) {
                throw new RuntimeException('Samo pobednik aukcije može potvrditi prijem.');
            }

            $amount = (float) $auction->current_price;

            // Skini kupcu locked_balance
            $buyer = User::lockForUpdate()->findOrFail($buyer->id);
            $buyer->locked_balance = (float) $buyer->locked_balance - $amount;
            $buyer->save();

            Transaction::create([
                'user_id' => $buyer->id,
                'auction_id' => $auction->id,
                'type' => 'payout',
                'amount' => -$amount,
                'balance_after' => $buyer->balance,
                'locked_balance_after' => $buyer->locked_balance,
                'note' => 'Isplata prodavcu za aukciju #' . $auction->id,
            ]);

            // Dodaj prodavcu balance
            $seller = User::lockForUpdate()->findOrFail($auction->seller_id);
            $seller->balance = (float) $seller->balance + $amount;
            $seller->save();

            Transaction::create([
                'user_id' => $seller->id,
                'auction_id' => $auction->id,
                'type' => 'payout',
                'amount' => $amount,
                'balance_after' => $seller->balance,
                'locked_balance_after' => $seller->locked_balance,
                'note' => 'Isplata za prodatu aukciju #' . $auction->id,
            ]);

            $auction->status = 'completed';
            $auction->save();
        });
    }

    /**
     * Admin oslobađa novac kupcu (vraca u balance).
     * Koristi se za razresavanje sporova u korist kupca, ili za otkazivanje aukcije.
     */
    public function refundBuyer(Auction $auction, string $note): void
    {
        DB::transaction(function () use ($auction, $note) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if (!$auction->winner_id) {
                throw new RuntimeException('Aukcija nema pobednika za refund.');
            }

            $amount = (float) $auction->current_price;
            $buyer = User::lockForUpdate()->findOrFail($auction->winner_id);

            $buyer->locked_balance = (float) $buyer->locked_balance - $amount;
            $buyer->balance = (float) $buyer->balance + $amount;
            $buyer->save();

            Transaction::create([
                'user_id' => $buyer->id,
                'auction_id' => $auction->id,
                'type' => 'refund',
                'amount' => $amount,
                'balance_after' => $buyer->balance,
                'locked_balance_after' => $buyer->locked_balance,
                'note' => $note,
            ]);
        });
    }

    /**
     * Dodavanje sredstava na korisnicki balans (deposit).
     * Za seminarski - admin ili korisnik moze "uplatiti" virtuelno.
     */
    public function deposit(User $user, float $amount, string $note = 'Uplata na balans'): Transaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Iznos mora biti pozitivan.');
        }

        return DB::transaction(function () use ($user, $amount, $note) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            $user->balance = (float) $user->balance + $amount;
            $user->save();

            return Transaction::create([
                'user_id' => $user->id,
                'auction_id' => null,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_after' => $user->balance,
                'locked_balance_after' => $user->locked_balance,
                'note' => $note,
            ]);
        });
    }

    /**
     * Interna helper metoda - oslobadja zakljucana sredstva korisniku.
     */
    private function unlockFundsForUser(int $userId, float $amount, int $auctionId, string $note): void
    {
        $user = User::lockForUpdate()->findOrFail($userId);
        $user->locked_balance = (float) $user->locked_balance - $amount;
        $user->balance = (float) $user->balance + $amount;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'auction_id' => $auctionId,
            'type' => 'unlock',
            'amount' => $amount,
            'balance_after' => $user->balance,
            'locked_balance_after' => $user->locked_balance,
            'note' => $note,
        ]);
    }
}
