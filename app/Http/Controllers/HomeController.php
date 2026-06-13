<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredAuctions = Auction::active()
            ->with(['primaryImage', 'category', 'seller'])
            ->withCount('bids')
            ->orderByDesc('bids_count')
            ->limit(6)
            ->get();

        $endingSoon = Auction::active()
            ->with(['primaryImage', 'category'])
            ->orderBy('ends_at')
            ->limit(4)
            ->get();

        $categories = Category::withCount(['auctions' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        return view('home', compact('featuredAuctions', 'endingSoon', 'categories'));
    }
}
