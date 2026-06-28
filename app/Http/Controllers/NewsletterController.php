<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Unesite email adresu.',
            'email.email' => 'Unesite ispravnu email adresu.',
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $data['email']]);

        if ($subscriber->wasRecentlyCreated) {
            return back()->with('success', 'Hvala! Prijavljeni ste na newsletter.');
        }

        return back()->with('success', 'Već ste prijavljeni na newsletter.');
    }
}
