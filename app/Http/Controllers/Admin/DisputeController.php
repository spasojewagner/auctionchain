<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EscrowException;
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

        try {
            DB::transaction(function () use ($dispute, $data) {
                $auction = $dispute->auction;

                if ($data['decision'] === 'buyer') {
                    // Novac se vraća kupcu, aukcija se otkazuje
                    $this->escrow->resolveForBuyer($auction, 'Spor #' . $dispute->id . ' rešen u korist kupca');
                    $dispute->status = 'resolved_for_buyer';
                } else {
                    // Novac iz escrow-a ide prodavcu, aukcija je završena
                    $this->escrow->resolveForSeller($auction);
                    $dispute->status = 'resolved_for_seller';
                }

                $dispute->resolution = $data['resolution'];
                $dispute->resolved_by = Auth::id();
                $dispute->save();

                // Notifikuj obe strane
                foreach ([$auction->seller_id, $auction->winner_id] as $userId) {
                    Notification::create([
                        'user_id' => $userId,
                        'auction_id' => $auction->id,
                        'type' => 'dispute_resolved',
                        'message' => "Spor za aukciju '{$auction->title}' je rešen.",
                    ]);
                }
            });
        } catch (EscrowException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.disputes.index')->with('success', 'Spor uspešno rešen.');
    }
}
