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
    public function placeBid(User $bidder, Auction $auction, float $amount): Bid
    {
        return DB::transaction(function () use ($bidder, $auction, $amount) {
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

            $minimumBid = $hasBids ? $currentHighest + 0.01 : (float) $auction->starting_price;

            if ($amount < $minimumBid) {
                throw new InvalidArgumentException("Ponuda mora biti najmanje " . number_format($minimumBid, 2) . " RSD.");
            }

            $previousHighest = $auction->bids()->orderByDesc('amount')->first();
            if ($previousHighest && $previousHighest->user_id === $bidder->id) {
                throw new InvalidArgumentException('Već imate najvišu ponudu na ovoj aukciji.');
            }

            if ((float) $bidder->balance < $amount) {
                throw new RuntimeException('Nemate dovoljno sredstava na balansu. Potrebno: ' . number_format($amount, 2) . ' RSD, dostupno: ' . number_format($bidder->balance, 2) . ' RSD.');
            }

            if ($previousHighest) {
                $this->unlockFundsForUser(
                    $previousHighest->user_id,
                    (float) $previousHighest->amount,
                    $auction->id,
                    'Nadlicitirani ste na aukciji #' . $auction->id
                );

                Notification::create([
                    'user_id' => $previousHighest->user_id,
                    'auction_id' => $auction->id,
                    'type' => 'outbid',
                    'message' => "Nadlicitirani ste na aukciji '{$auction->title}'.",
                ]);
            }

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
     * "Kupi odmah" - korisnik momentalno kupuje predmet po buy_now ceni.
     * Aukcija se odmah završava, kupac postaje pobednik, a sredstva se
     * zaključavaju u escrow (kao kod normalne pobede) do potvrde prijema.
     */
    public function buyNow(User $buyer, Auction $auction): void
    {
        DB::transaction(function () use ($buyer, $auction) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);
            $buyer = User::lockForUpdate()->findOrFail($buyer->id);

            if (!$auction->isActive()) {
                throw new RuntimeException('Aukcija nije aktivna.');
            }
            if (is_null($auction->buy_now_price)) {
                throw new RuntimeException('Ova aukcija nema "Kupi odmah" cenu.');
            }
            if ($auction->seller_id === $buyer->id) {
                throw new InvalidArgumentException('Ne možete kupiti sopstvenu aukciju.');
            }
            if ($buyer->is_suspended) {
                throw new RuntimeException('Vaš nalog je suspendovan.');
            }

            $price = (float) $auction->buy_now_price;

            if ((float) $auction->current_price >= $price && $auction->bids()->exists()) {
                throw new RuntimeException('Trenutna ponuda je već dostigla "Kupi odmah" cenu.');
            }

            if ((float) $buyer->balance < $price) {
                throw new RuntimeException('Nemate dovoljno sredstava. Potrebno: ' . number_format($price, 2) . ' RSD, dostupno: ' . number_format($buyer->balance, 2) . ' RSD.');
            }

            // Vrati prethodnom najvišem ponuđaču zaključana sredstva
            $previousHighest = $auction->bids()->orderByDesc('amount')->first();
            if ($previousHighest && $previousHighest->user_id !== $buyer->id) {
                $this->unlockFundsForUser(
                    $previousHighest->user_id,
                    (float) $previousHighest->amount,
                    $auction->id,
                    'Aukcija #' . $auction->id . ' kupljena preko "Kupi odmah"'
                );

                Notification::create([
                    'user_id' => $previousHighest->user_id,
                    'auction_id' => $auction->id,
                    'type' => 'outbid',
                    'message' => "Aukcija '{$auction->title}' je kupljena preko 'Kupi odmah'.",
                ]);
            }

            // Zaključaj sredstva kupca
            $buyer->balance = (float) $buyer->balance - $price;
            $buyer->locked_balance = (float) $buyer->locked_balance + $price;
            $buyer->save();

            Transaction::create([
                'user_id' => $buyer->id,
                'auction_id' => $auction->id,
                'type' => 'lock',
                'amount' => $price,
                'balance_after' => $buyer->balance,
                'locked_balance_after' => $buyer->locked_balance,
                'note' => 'Kupi odmah - aukcija #' . $auction->id,
            ]);

            // Zabeleži kao ponudu radi istorije
            Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $buyer->id,
                'amount' => $price,
            ]);

            // Završi aukciju odmah
            $auction->current_price = $price;
            $auction->status = 'ended';
            $auction->winner_id = $buyer->id;
            $auction->save();

            Notification::create([
                'user_id' => $buyer->id,
                'auction_id' => $auction->id,
                'type' => 'auction_won',
                'message' => "Kupili ste '{$auction->title}' preko 'Kupi odmah' za " . number_format($price, 2) . " RSD.",
            ]);

            Notification::create([
                'user_id' => $auction->seller_id,
                'auction_id' => $auction->id,
                'type' => 'auction_ended_seller',
                'message' => "Vaša aukcija '{$auction->title}' je prodata preko 'Kupi odmah' za " . number_format($price, 2) . " RSD.",
            ]);
        });
    }

    public function endAuction(Auction $auction): void
    {
        DB::transaction(function () use ($auction) {
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            if ($auction->status !== 'active') {
                return;
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
