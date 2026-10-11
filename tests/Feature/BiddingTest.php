<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Tests\TestCase;

class BiddingTest extends TestCase
{
    public function test_a_bid_locks_the_amount_in_escrow(): void
    {
        $seller = $this->user();
        $bidder = $this->user(5000);
        $auction = $this->auction($seller);

        $this->actingAs($bidder)
            ->postJson("/auctions/{$auction->id}/bids", ['amount' => 1200])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([3800.0, 1200.0], $this->wallet($bidder));
        $this->assertSame('1200.00', $auction->fresh()->current_price);
        $this->assertDatabaseHas('transactions', ['user_id' => $bidder->id, 'type' => 'lock', 'amount' => 1200]);
    }

    public function test_being_outbid_returns_the_locked_amount(): void
    {
        $seller = $this->user();
        $first = $this->user(5000);
        $second = $this->user(5000);
        $auction = $this->auction($seller);

        $this->actingAs($first)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1200])->assertOk();
        $this->actingAs($second)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1500])->assertOk();

        $this->assertSame([5000.0, 0.0], $this->wallet($first));
        $this->assertSame([3500.0, 1500.0], $this->wallet($second));
        $this->assertDatabaseHas('notifications', ['user_id' => $first->id, 'type' => 'outbid']);
        $this->assertSame(10000.0, $this->totalMoney());
    }

    public function test_bids_that_break_the_rules_are_rejected_without_moving_money(): void
    {
        $seller = $this->user(5000);
        $bidder = $this->user(5000);
        $poor = $this->user(100);
        $auction = $this->auction($seller);

        // Below the starting price
        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 999])
            ->assertStatus(422)->assertJson(['success' => false]);
        // Own auction
        $this->actingAs($seller)->postJson("/auctions/{$auction->id}/bids", ['amount' => 2000])
            ->assertStatus(422);
        // Not enough money
        $this->actingAs($poor)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1000])
            ->assertStatus(422);

        // Outbidding yourself
        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1000])->assertOk();
        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1100])
            ->assertStatus(422);

        $this->assertSame([4000.0, 1000.0], $this->wallet($bidder));
        $this->assertSame([5000.0, 0.0], $this->wallet($seller));
        $this->assertSame(1, Transaction::count());
    }

    public function test_a_bid_on_an_ended_auction_is_rejected(): void
    {
        $bidder = $this->user(5000);
        $auction = $this->auction($this->user(), ['ends_at' => now()->subMinute()]);

        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1500])
            ->assertStatus(422);

        $this->assertSame([5000.0, 0.0], $this->wallet($bidder));
    }

    public function test_buy_now_ends_the_auction_and_refunds_the_previous_bidder(): void
    {
        $seller = $this->user();
        $bidder = $this->user(5000);
        $buyer = $this->user(5000);
        $auction = $this->auction($seller, ['buy_now_price' => 3000]);

        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1200])->assertOk();
        $this->actingAs($buyer)->post("/auctions/{$auction->id}/buy-now")->assertRedirect();

        $auction->refresh();
        $this->assertSame('ended', $auction->status);
        $this->assertSame($buyer->id, $auction->winner_id);
        $this->assertSame([5000.0, 0.0], $this->wallet($bidder));
        $this->assertSame([2000.0, 3000.0], $this->wallet($buyer));
    }

    public function test_an_expired_auction_is_closed_and_the_highest_bidder_wins(): void
    {
        $seller = $this->user();
        $bidder = $this->user(5000);
        $auction = $this->auction($seller);
        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1200])->assertOk();

        $auction->update(['ends_at' => now()->subMinute()]);
        $this->get('/')->assertOk(); // any page runs the middleware that closes expired auctions

        $auction->refresh();
        $this->assertSame('ended', $auction->status);
        $this->assertSame($bidder->id, $auction->winner_id);
        $this->assertSame([3800.0, 1200.0], $this->wallet($bidder)); // still in escrow
    }
}
