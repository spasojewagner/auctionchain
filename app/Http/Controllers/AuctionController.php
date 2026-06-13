<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuctionController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    /**
     * Lista aukcija sa filtriranjem i pretragom.
     */
    public function index(Request $request)
    {
        $query = Auction::with(['primaryImage', 'category', 'seller'])
            ->withCount('bids');

        // Filter po statusu (default: active)
        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'ended') {
            $query->ended();
        }

        // Filter po kategoriji
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Cenovni opseg
        if ($request->filled('min_price')) {
            $query->where('current_price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('current_price', '<=', $request->input('max_price'));
        }

        // Pretraga po nazivu
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sortiranje
        $sort = $request->input('sort', 'ends_soon');
        match ($sort) {
            'newest' => $query->orderByDesc('created_at'),
            'price_low' => $query->orderBy('current_price'),
            'price_high' => $query->orderByDesc('current_price'),
            'most_bids' => $query->orderByDesc('bids_count'),
            default => $query->orderBy('ends_at'),
        };

        $auctions = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('auctions.index', compact('auctions', 'categories'));
    }

    public function show(Auction $auction)
    {
        $auction->load(['images', 'category', 'seller', 'bids.user', 'winner']);
        $highestBid = $auction->bids()->orderByDesc('amount')->first();

        return view('auctions.show', compact('auction', 'highestBid'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('auctions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'starting_price' => ['required', 'numeric', 'min:1'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:720'], // max 30 dana
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $auction = Auction::create([
            'seller_id' => Auth::id(),
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'starting_price' => $data['starting_price'],
            'current_price' => $data['starting_price'],
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addHours((int) $data['duration_hours']),
        ]);

        foreach ($request->file('images') as $index => $image) {
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/auctions'), $filename);

            AuctionImage::create([
                'auction_id' => $auction->id,
                'path' => 'auctions/' . $filename,
                'is_primary' => $index === 0,
            ]);
        }

        return redirect()->route('auctions.show', $auction)
            ->with('success', 'Aukcija uspešno kreirana!');
    }

    public function destroy(Auction $auction)
    {
        if ($auction->seller_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($auction->bids()->exists()) {
            return back()->withErrors(['error' => 'Ne možete obrisati aukciju koja ima ponude.']);
        }

        // Obrisi slike sa diska
        foreach ($auction->images as $image) {
            $path = public_path('uploads/' . $image->path);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $auction->delete();
        return redirect()->route('auctions.index')->with('success', 'Aukcija obrisana.');
    }

    /**
     * AJAX endpoint za potvrdu prijema robe od strane kupca.
     */
    public function confirmDelivery(Auction $auction)
    {
        try {
            $this->escrow->confirmDelivery($auction, Auth::user());
            return back()->with('success', 'Prijem potvrđen. Sredstva su prebačena prodavcu.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
