<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use Tests\TestCase;

class EscrowReleaseTest extends TestCase
{
    /** An auction that has ended with a winner, the winning amount locked in escrow. */
    private function wonAuction(): array
    {
        $seller = $this->user();
        $buyer = $this->user(5000);
        $auction = $this->auction($seller);

        $this->actingAs($buyer)->postJson("/auctions/{$auction->id}/bids", ['amount' => 2000])->assertOk();
        $auction->update(['ends_at' => now()->subMinute()]);
        $this->get('/');

        return [$seller, $buyer, $auction->fresh()];
    }

    public function test_confirming_delivery_pays_the_seller(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();

        $this->actingAs($buyer)->post("/auctions/{$auction->id}/confirm-delivery")->assertSessionHasNoErrors();

        $this->assertSame('completed', $auction->fresh()->status);
        $this->assertSame([3000.0, 0.0], $this->wallet($buyer));
        $this->assertSame([2000.0, 0.0], $this->wallet($seller));
        $this->assertSame(5000.0, $this->totalMoney());
    }

    public function test_only_the_winner_can_confirm_delivery(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();

        $this->actingAs($seller)->post("/auctions/{$auction->id}/confirm-delivery")->assertSessionHasErrors();

        $this->assertSame('ended', $auction->fresh()->status);
        $this->assertSame([0.0, 0.0], $this->wallet($seller));
    }

    public function test_delivery_cannot_be_confirmed_twice(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();

        $this->actingAs($buyer)->post("/auctions/{$auction->id}/confirm-delivery");
        $this->actingAs($buyer)->post("/auctions/{$auction->id}/confirm-delivery")->assertSessionHasErrors();

        $this->assertSame([2000.0, 0.0], $this->wallet($seller));
    }

    public function test_a_dispute_resolved_for_the_seller_pays_the_seller(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();
        $admin = $this->user(0, 'admin');

        $this->actingAs($buyer)->post("/auctions/{$auction->id}/dispute", ['reason' => 'Predmet nije stigao ni posle dve nedelje.']);
        $this->assertSame('disputed', $auction->fresh()->status);

        $dispute = $auction->fresh()->dispute;
        $this->actingAs($admin)->post("/admin/disputes/{$dispute->id}/resolve", [
            'decision' => 'seller',
            'resolution' => 'Prodavac je dostavio dokaz o slanju.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $auction->fresh()->status);
        $this->assertSame('resolved_for_seller', $dispute->fresh()->status);
        $this->assertSame([2000.0, 0.0], $this->wallet($seller));
        $this->assertSame([3000.0, 0.0], $this->wallet($buyer));
    }

    public function test_a_dispute_resolved_for_the_buyer_refunds_the_buyer(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();
        $admin = $this->user(0, 'admin');

        $this->actingAs($buyer)->post("/auctions/{$auction->id}/dispute", ['reason' => 'Predmet nije stigao ni posle dve nedelje.']);
        $dispute = $auction->fresh()->dispute;
        $this->actingAs($admin)->post("/admin/disputes/{$dispute->id}/resolve", [
            'decision' => 'buyer',
            'resolution' => 'Prodavac nije dostavio dokaz o slanju.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $auction->fresh()->status);
        $this->assertSame([5000.0, 0.0], $this->wallet($buyer));
        $this->assertSame([0.0, 0.0], $this->wallet($seller));
    }

    public function test_no_dispute_after_the_seller_has_been_paid(): void
    {
        [$seller, $buyer, $auction] = $this->wonAuction();
        $this->actingAs($buyer)->post("/auctions/{$auction->id}/confirm-delivery");

        // A dispute now, resolved for the buyer, would hand out the same money twice.
        $this->actingAs($buyer)
            ->post("/auctions/{$auction->id}/dispute", ['reason' => 'Ipak želim povraćaj novca za ovaj predmet.'])
            ->assertSessionHasErrors();

        $this->assertSame('completed', $auction->fresh()->status);
        $this->assertNull($auction->fresh()->dispute);
    }

    public function test_no_dispute_on_an_auction_that_is_still_running(): void
    {
        $seller = $this->user();
        $auction = $this->auction($seller);

        $this->actingAs($seller)
            ->post("/auctions/{$auction->id}/dispute", ['reason' => 'Pokušaj da se zaustavi aukcija u toku.'])
            ->assertSessionHasErrors();

        $this->assertSame('active', $auction->fresh()->status);
    }

    public function test_removing_an_auction_returns_the_locked_money(): void
    {
        $seller = $this->user();
        $bidder = $this->user(5000);
        $admin = $this->user(0, 'admin');
        $auction = $this->auction($seller);
        $this->actingAs($bidder)->postJson("/auctions/{$auction->id}/bids", ['amount' => 1500])->assertOk();

        $this->actingAs($admin)->delete("/admin/auctions/{$auction->id}")->assertRedirect();

        $this->assertNull(Auction::find($auction->id));
        $this->assertSame([5000.0, 0.0], $this->wallet($bidder));
    }
}
