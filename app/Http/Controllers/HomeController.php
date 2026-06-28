<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['auctions' => function ($q) {
            $q->where('status', 'active')->where('ends_at', '>', now());
        }])->orderBy('name')->get();

        // Promovisane = aktivne aukcije sa najviše ponuda
        $promotedAuctions = Auction::active()
            ->with(['primaryImage', 'images', 'category', 'seller'])
            ->withCount('bids')
            ->orderByDesc('bids_count')
            ->take(6)
            ->get();

        // Kupi odmah ponude
        $buyNowAuctions = Auction::active()
            ->with(['primaryImage', 'images', 'category', 'seller'])
            ->withCount('bids')
            ->whereNotNull('buy_now_price')
            ->orderBy('ends_at')
            ->take(4)
            ->get();

        // Uskoro se završavaju
        $endingSoon = Auction::active()
            ->with(['primaryImage', 'images', 'category', 'seller'])
            ->withCount('bids')
            ->orderBy('ends_at')
            ->take(4)
            ->get();

        // Nove na platformi
        $newestAuctions = Auction::active()
            ->with(['primaryImage', 'images', 'category', 'seller'])
            ->withCount('bids')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        return view('home', compact(
            'categories',
            'promotedAuctions',
            'buyNowAuctions',
            'endingSoon',
            'newestAuctions'
        ));
    }
}
