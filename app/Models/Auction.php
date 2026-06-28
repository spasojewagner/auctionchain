<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'winner_id',
        'title',
        'description',
        'starting_price',
        'current_price',
        'buy_now_price',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'starting_price' => 'decimal:2',
            'current_price' => 'decimal:2',
            'buy_now_price' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(AuctionImage::class)->where('is_primary', true);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->orderByDesc('amount');
    }

    public function highestBid(): HasOne
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('ends_at', '>', now());
    }

    public function scopeEnded(Builder $query): Builder
    {
        return $query->whereIn('status', ['ended', 'completed', 'disputed', 'cancelled']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast() || $this->status !== 'active';
    }

    /**
     * Da li je "Kupi odmah" opcija trenutno dostupna.
     * Dostupna je samo dok je aukcija aktivna, ima buy_now cenu,
     * i trenutna ponuda još nije dostigla tu cenu.
     */
    public function buyNowAvailable(): bool
    {
        return $this->isActive()
            && $this->buy_now_price !== null
            && (float) $this->current_price < (float) $this->buy_now_price;
    }

    public function timeRemaining(): string
    {
        if ($this->hasEnded()) {
            return 'Završeno';
        }
        return $this->ends_at->diffForHumans(['parts' => 2, 'short' => true]);
    }
}
