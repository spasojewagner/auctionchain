<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AuctionController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function index(Request $request)
    {
        $query = Auction::with(['primaryImage', 'category', 'seller'])
            ->withCount('bids');

        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'ended') {
            $query->ended();
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }
        if ($request->filled('min_price')) {
            $query->where('current_price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('current_price', '<=', $request->input('max_price'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

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
            'buy_now_price' => ['nullable', 'numeric', 'gt:starting_price'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:720'],
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
            'buy_now_price' => $data['buy_now_price'] ?? null,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addHours((int) $data['duration_hours']),
        ]);

        foreach ($request->file('images') as $index => $image) {
            $this->saveImage($auction, $image, $index === 0);
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
        foreach ($auction->images as $image) {
            $this->deleteImageFile($image);
        }
        $auction->delete();
        return redirect()->route('auctions.index')->with('success', 'Aukcija obrisana.');
    }

    public function confirmDelivery(Auction $auction)
    {
        try {
            $this->escrow->confirmDelivery($auction, Auth::user());
            return back()->with('success', 'Prijem potvrđen. Sredstva su prebačena prodavcu.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function buyNow(Auction $auction)
    {
        try {
            $this->escrow->buyNow(Auth::user(), $auction);
            return redirect()->route('auctions.show', $auction)
                ->with('success', 'Čestitamo! Kupili ste predmet. Iznos je zaključan u escrow-u dok ne potvrdite prijem.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /* ===================== UREĐIVANJE SLIKA ===================== */

    public function edit(Auction $auction)
    {
        $this->authorizeOwner($auction);
        $auction->load('images');
        return view('auctions.edit', compact('auction'));
    }

    public function storeImages(Request $request, Auction $auction)
    {
        $this->authorizeOwner($auction);

        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $existing = $auction->images()->count();
        $incoming = count($request->file('images'));

        if ($existing + $incoming > 5) {
            return back()->withErrors(['error' => "Maksimalno 5 slika po aukciji. Trenutno imate {$existing}."]);
        }

        foreach ($request->file('images') as $image) {
            $this->saveImage($auction, $image, $existing === 0);
            $existing++;
        }

        return back()->with('success', 'Slike uspešno dodate.');
    }

    public function destroyImage(Auction $auction, AuctionImage $image)
    {
        $this->authorizeOwner($auction);
        if ($image->auction_id !== $auction->id) {
            abort(404);
        }
        if ($auction->images()->count() <= 1) {
            return back()->withErrors(['error' => 'Aukcija mora imati bar jednu sliku.']);
        }

        $wasPrimary = $image->is_primary;
        $this->deleteImageFile($image);
        $image->delete();

        if ($wasPrimary) {
            $first = $auction->images()->first();
            if ($first) {
                $first->is_primary = true;
                $first->save();
            }
        }

        return back()->with('success', 'Slika obrisana.');
    }

    public function setPrimaryImage(Auction $auction, AuctionImage $image)
    {
        $this->authorizeOwner($auction);
        if ($image->auction_id !== $auction->id) {
            abort(404);
        }
        $auction->images()->update(['is_primary' => false]);
        $image->is_primary = true;
        $image->save();

        return back()->with('success', 'Glavna slika postavljena.');
    }

    /* ===================== HELPERI ===================== */

    private function authorizeOwner(Auction $auction): void
    {
        if ($auction->seller_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    private function saveImage(Auction $auction, $image, bool $isPrimary): void
    {
        $destDir = public_path('uploads/auctions');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $destPath = $destDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($image->getRealPath());
            $img->scaleDown(width: 1200);
            $img->save($destPath, quality: 82);
        } catch (\Throwable $e) {
            $image->move($destDir, $filename);
        }

        AuctionImage::create([
            'auction_id' => $auction->id,
            'path' => 'auctions/' . $filename,
            'is_primary' => $isPrimary,
        ]);
    }

    private function deleteImageFile(AuctionImage $image): void
    {
        if ($image->path === 'placeholder.jpg') {
            return; // ne brisati zajednički placeholder
        }
        $path = public_path('uploads/' . $image->path);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
