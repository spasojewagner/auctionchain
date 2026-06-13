<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['seller', 'category'])->withCount('bids');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $auctions = $query->latest()->paginate(20)->withQueryString();
        return view('admin.auctions.index', compact('auctions'));
    }

    public function destroy(Auction $auction)
    {
        // Obrisi slike
        foreach ($auction->images as $image) {
            $path = public_path('uploads/' . $image->path);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $auction->delete();
        return back()->with('success', 'Aukcija obrisana.');
    }
}
