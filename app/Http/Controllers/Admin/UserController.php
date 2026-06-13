<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    public function index(Request $request)
    {
        $query = User::withCount(['auctions', 'bids']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function toggleSuspension(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Ne možete suspendovati sami sebe.']);
        }

        $user->is_suspended = !$user->is_suspended;
        $user->save();

        $msg = $user->is_suspended ? 'Korisnik suspendovan.' : 'Suspenzija uklonjena.';
        return back()->with('success', $msg);
    }

    public function adjustBalance(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        $this->escrow->deposit($user, (float) $data['amount'], 'Admin: ' . $data['note']);
        return back()->with('success', 'Balans korisnika ažuriran.');
    }
}
