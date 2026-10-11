<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AccountTest extends TestCase
{
    public function test_sign_up_creates_a_regular_account_with_an_empty_balance(): void
    {
        $this->post('/register', [
            'name' => 'Ana',
            'email' => 'ana@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin',
            'balance' => 1000000,
        ])->assertRedirect('/');

        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertSame(0.0, (float) $user->balance);
    }

    public function test_money_can_only_be_added_through_a_payment(): void
    {
        $user = $this->user();

        // The old endpoint that credited any amount without a payment is gone.
        $this->actingAs($user)->post('/profile/deposit', ['amount' => 1000000])->assertNotFound();

        $this->assertSame([0.0, 0.0], $this->wallet($user));
    }

    public function test_admin_pages_are_closed_to_regular_users(): void
    {
        $this->actingAs($this->user())->get('/admin')->assertForbidden();
        $this->actingAs($this->user(0, 'admin'))->get('/admin')->assertOk();
    }
}
