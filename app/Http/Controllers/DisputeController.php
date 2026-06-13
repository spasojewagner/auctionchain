<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Dispute;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function create(Auction $auction)
    {
        if ($auction->winner_id !== Auth::id() && $auction->seller_id !== Auth::id()) {
            abort(403, 'Samo kupac ili prodavac mogu otvoriti spor.');
        }

        if ($auction->status !== 'ended') {
            return back()->withErrors(['error' => 'Spor se može otvoriti samo za završene aukcije koje nisu finalizovane.']);
        }

        if ($auction->dispute()->exists()) {
            return back()->withErrors(['error' => 'Spor je već otvoren za ovu aukciju.']);
        }

        return view('auctions.dispute-create', compact('auction'));
    }

    public function store(Request $request, Auction $auction)
    {
        if ($auction->winner_id !== Auth::id() && $auction->seller_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        Dispute::create([
            'auction_id' => $auction->id,
            'opened_by' => Auth::id(),
            'reason' => $data['reason'],
            'status' => 'open',
        ]);

        $auction->status = 'disputed';
        $auction->save();

        // Notifikuj drugog ucesnika u aukciji
        $otherUserId = (Auth::id() === $auction->winner_id) ? $auction->seller_id : $auction->winner_id;
        Notification::create([
            'user_id' => $otherUserId,
            'auction_id' => $auction->id,
            'type' => 'dispute_opened',
            'message' => "Otvoren je spor za aukciju '{$auction->title}'.",
        ]);

        return redirect()->route('auctions.show', $auction)
            ->with('success', 'Spor je otvoren. Administrator će razmotriti.');
    }
}
