<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function show()
    {
        $user = Auth::user();
        $transactions = $user->transactions()->latest()->limit(20)->get();

        return view('profile.show', compact('user', 'transactions'));
    }

    public function myAuctions()
    {
        $myAuctions = Auth::user()->auctions()
            ->with(['primaryImage', 'category'])
            ->withCount('bids')
            ->latest()
            ->paginate(10);

        return view('profile.my-auctions', compact('myAuctions'));
    }

    public function myBids()
    {
        $myBids = Auth::user()->bids()
            ->with(['auction.primaryImage', 'auction.category'])
            ->latest()
            ->paginate(10);

        $wonAuctions = Auth::user()->wonAuctions()
            ->with(['primaryImage', 'category'])
            ->whereIn('status', ['ended', 'completed', 'disputed'])
            ->latest()
            ->limit(20)
            ->get();

        return view('profile.my-bids', compact('myBids', 'wonAuctions'));
    }

    /**
     * Instant (virtuelna) uplata - ostaje kao fallback.
     * Glavni tok uplate sada ide preko Stripe-a (DepositController).
     */
    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:1000000'],
        ]);

        $this->escrow->deposit(
            Auth::user(),
            (float) $data['amount'],
            'Korisnička uplata na balans'
        );

        return back()->with('success', 'Uplata uspešna! Balans ažuriran.');
    }

    /**
     * Čuva adresu MetaMask novčanika (AJAX poziv sa frontenda).
     */
    public function saveWallet(Request $request)
    {
        $data = $request->validate([
            'wallet_address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        $user = Auth::user();
        $user->wallet_address = $data['wallet_address'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Novčanik uspešno povezan!',
            'wallet' => $data['wallet_address'],
        ]);
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()
            ->with('auction')
            ->latest()
            ->paginate(20);

        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return view('profile.notifications', compact('notifications'));
    }

    public function notificationsCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
