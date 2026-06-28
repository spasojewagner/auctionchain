<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

/**
 * DepositController - uplata na balans preko Stripe Checkout-a (test mode).
 *
 * Tok:
 * 1. checkout()  - kreira Stripe Checkout sesiju i preusmerava na Stripe stranicu
 * 2. korisnik plaća test karticom (4242 4242 4242 4242)
 * 3. Stripe vraća na success() sa session_id u URL-u
 * 4. success() proverava da je plaćeno i knjiži uplatu na balans (preko EscrowService)
 *
 * Bez webhook-ova - provera se radi na success redirect-u (dovoljno za seminarski).
 */
class DepositController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:1000000'],
        ]);

        $amount = (float) $data['amount'];
        $currency = config('services.stripe.currency', 'rsd');

        if (!config('services.stripe.secret')) {
            return back()->withErrors(['error' => 'Stripe ključevi nisu podešeni u .env fajlu.']);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::create([
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Depozit na AuctionChain balans',
                        ],
                        'unit_amount' => (int) round($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('deposit.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('deposit.cancel'),
                'metadata' => [
                    'user_id' => Auth::id(),
                    'amount' => $amount,
                ],
            ]);

            return redirect($session->url);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Greška pri pokretanju Stripe plaćanja: ' . $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('profile.show');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Throwable $e) {
            return redirect()->route('profile.show')->withErrors(['error' => 'Ne mogu da proverim plaćanje.']);
        }

        // Mora biti plaćeno
        if (($session->payment_status ?? null) !== 'paid') {
            return redirect()->route('profile.show')->withErrors(['error' => 'Plaćanje nije potvrđeno.']);
        }

        // Mora pripadati ulogovanom korisniku
        if ((int) ($session->metadata->user_id ?? 0) !== Auth::id()) {
            return redirect()->route('profile.show')->withErrors(['error' => 'Plaćanje ne pripada ovom nalogu.']);
        }

        // Spreči dvostruko knjiženje (npr. refresh stranice)
        $already = Transaction::where('note', 'like', '%' . $sessionId . '%')->exists();
        if ($already) {
            return redirect()->route('profile.show')->with('success', 'Uplata je već proknjižena.');
        }

        $amount = (float) ($session->metadata->amount ?? 0);
        if ($amount > 0) {
            $this->escrow->deposit(Auth::user(), $amount, 'Stripe uplata (' . $sessionId . ')');
        }

        return redirect()->route('profile.show')->with('success', 'Uplata uspešna! Balans je ažuriran.');
    }

    public function cancel()
    {
        return redirect()->route('profile.show')->with('success', 'Plaćanje je otkazano.');
    }
}
