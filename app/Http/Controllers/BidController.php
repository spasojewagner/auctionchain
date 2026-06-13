<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    /**
     * AJAX endpoint za postavljanje ponude.
     * Vraca JSON sa rezultatom i azuriranim stanjem aukcije.
     */
    public function store(Request $request, Auction $auction): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $bid = $this->escrow->placeBid(Auth::user(), $auction, (float) $data['amount']);
            $auction->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Ponuda uspešno postavljena!',
                'bid' => [
                    'id' => $bid->id,
                    'amount' => number_format((float) $bid->amount, 2),
                    'user_name' => Auth::user()->name,
                    'created_at' => $bid->created_at->diffForHumans(),
                ],
                'auction' => [
                    'current_price' => number_format((float) $auction->current_price, 2),
                    'bid_count' => $auction->bids()->count(),
                ],
                'user' => [
                    'balance' => number_format((float) Auth::user()->fresh()->balance, 2),
                    'locked_balance' => number_format((float) Auth::user()->fresh()->locked_balance, 2),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * AJAX polling endpoint - vraca trenutno stanje aukcije.
     * Frontend ga zove svakih 3s da bi prikazao live ponude.
     */
    public function poll(Auction $auction): JsonResponse
    {
        $bids = $auction->bids()
            ->with('user:id,name')
            ->orderByDesc('amount')
            ->limit(10)
            ->get()
            ->map(fn ($bid) => [
                'id' => $bid->id,
                'amount' => number_format((float) $bid->amount, 2),
                'user_name' => $bid->user->name,
                'created_at' => $bid->created_at->diffForHumans(),
                'is_mine' => Auth::check() && $bid->user_id === Auth::id(),
            ]);

        return response()->json([
            'current_price' => number_format((float) $auction->current_price, 2),
            'bid_count' => $auction->bids()->count(),
            'time_remaining' => $auction->timeRemaining(),
            'status' => $auction->status,
            'is_active' => $auction->isActive(),
            'ends_at' => $auction->ends_at->toIso8601String(),
            'bids' => $bids,
        ]);
    }
}
