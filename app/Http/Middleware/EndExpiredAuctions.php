<?php

namespace App\Http\Middleware;

use App\Models\Auction;
use App\Services\EscrowService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware koji nalazi aukcije kojima je isteklo vreme i finalizuje ih.
 * Radi se kroz middleware (umesto cron-a) jer je seminarski rad - jednostavnije
 * je za lokalno pokretanje. U produkciji bi se ovo radilo kroz scheduled job.
 */
class EndExpiredAuctions
{
    public function __construct(private EscrowService $escrow) {}

    public function handle(Request $request, Closure $next): Response
    {
        $expired = Auction::where('status', 'active')
            ->where('ends_at', '<=', now())
            ->limit(20)
            ->get();

        foreach ($expired as $auction) {
            try {
                $this->escrow->endAuction($auction);
            } catch (\Throwable $e) {
                logger()->error("Greska pri zavrsavanju aukcije {$auction->id}: " . $e->getMessage());
            }
        }

        return $next($request);
    }
}
