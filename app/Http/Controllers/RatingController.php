<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Rating;
use Illuminate\Http\Request;

/**
 * Ocenjivanje prodavaca i kupaca nakon završene (completed) aukcije.
 * Kupac (pobednik) ocenjuje prodavca, prodavac ocenjuje kupca.
 */
class RatingController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $data = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'stars.required' => 'Izaberite ocenu (1-5 zvezdica).',
            'stars.min' => 'Ocena mora biti između 1 i 5.',
            'stars.max' => 'Ocena mora biti između 1 i 5.',
            'comment.max' => 'Komentar može imati najviše 500 karaktera.',
        ]);

        $user = $request->user();

        // Ocenjivanje je moguće tek kad je transakcija završena (potvrđen prijem)
        if ($auction->status !== 'completed' || !$auction->winner_id) {
            return back()->with('error', 'Ocenjivanje je moguće tek nakon što je transakcija završena.');
        }

        // Samo učesnici transakcije mogu da ocenjuju
        if ($user->id === $auction->seller_id) {
            $ratedUserId = $auction->winner_id;   // prodavac ocenjuje kupca
        } elseif ($user->id === $auction->winner_id) {
            $ratedUserId = $auction->seller_id;   // kupac ocenjuje prodavca
        } else {
            return back()->with('error', 'Samo kupac i prodavac mogu da ocenjuju ovu transakciju.');
        }

        // Jedna ocena po aukciji po korisniku; ponovno slanje menja postojeću
        Rating::updateOrCreate(
            ['auction_id' => $auction->id, 'rater_id' => $user->id],
            ['rated_user_id' => $ratedUserId, 'stars' => $data['stars'], 'comment' => $data['comment'] ?? null]
        );

        return back()->with('success', 'Ocena je sačuvana. Hvala!');
    }
}
