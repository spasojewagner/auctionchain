<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_suspended', false)->count(),
            'total_auctions' => Auction::count(),
            'active_auctions' => Auction::where('status', 'active')->count(),
            'completed_auctions' => Auction::where('status', 'completed')->count(),
            'total_bids' => Bid::count(),
            'total_volume' => Auction::where('status', 'completed')->sum('current_price'),
            'open_disputes' => Dispute::where('status', 'open')->count(),
        ];

        $topCategories = DB::table('auctions')
            ->join('categories', 'auctions.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(auctions.id) as auction_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('auction_count')
            ->limit(5)
            ->get();

        $recentAuctions = Auction::with(['seller', 'category'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'topCategories', 'recentAuctions'));
    }
}
