<?php

namespace Tests;

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function user(float $balance = 0, string $role = 'user'): User
    {
        $this->seq++;

        return User::create([
            'name' => "User {$this->seq}",
            'email' => "user{$this->seq}@example.test",
            'password' => 'password',
            'role' => $role,
            'balance' => $balance,
            'locked_balance' => 0,
        ]);
    }

    protected function auction(User $seller, array $overrides = []): Auction
    {
        $category = Category::firstOrCreate(['slug' => 'elektronika'], ['name' => 'Elektronika']);

        return Auction::create(array_merge([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Polovni laptop',
            'description' => 'Ispravan, sa punjačem.',
            'starting_price' => 1000,
            'current_price' => 1000,
            'status' => 'active',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
        ], $overrides));
    }

    /** Balance and locked balance of a user, fresh from the database. */
    protected function wallet(User $user): array
    {
        $user->refresh();

        return [(float) $user->balance, (float) $user->locked_balance];
    }

    /** All money in the system: nothing may appear or disappear except through deposits. */
    protected function totalMoney(): float
    {
        return (float) User::sum('balance') + (float) User::sum('locked_balance');
    }
}
