<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Notification;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function index()
    {
        $disputes = Dispute::with(['auction.seller', 'auction.winner', 'opener'])
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['auction.seller', 'auction.winner', 'auction.bids.user', 'opener', 'resolver']);
        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:buyer,seller'],
            'resolution' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        if ($dispute->status !== 'open') {
            return back()->withErrors(['error' => 'Spor je već rešen.']);
        }

        DB::transaction(function () use ($dispute, $data) {
            $auction = $dispute->auction;

            if ($data['decision'] === 'buyer') {
                // Vrati novac kupcu
                $this->escrow->refundBuyer($auction, 'Spor #' . $dispute->id . ' rešen u korist kupca');
                $dispute->status = 'resolved_for_buyer';
                $auction->status = 'cancelled';
            } else {
                // Isplata prodavcu
                $this->escrow->confirmDelivery($auction, $auction->winner);
                $dispute->status = 'resolved_for_seller';
                // confirmDelivery vec postavlja status na 'completed'
            }

            $dispute->resolution = $data['resolution'];
            $dispute->resolved_by = Auth::id();
            $dispute->save();
            $auction->save();

            // Notifikuj obe strane
            Notification::create([
                'user_id' => $auction->seller_id,
                'auction_id' => $auction->id,
                'type' => 'dispute_resolved',
                'message' => "Spor za aukciju '{$auction->title}' je rešen.",
            ]);
            Notification::create([
                'user_id' => $auction->winner_id,
                'auction_id' => $auction->id,
                'type' => 'dispute_resolved',
                'message' => "Spor za aukciju '{$auction->title}' je rešen.",
            ]);
        });

        return redirect()->route('admin.disputes.index')->with('success', 'Spor uspešno rešen.');
    }
}
