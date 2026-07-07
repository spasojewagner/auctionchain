<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = ['auction_id', 'rater_id', 'rated_user_id', 'stars', 'comment'];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    /** Korisnik koji je dao ocenu */
    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /** Korisnik koji je ocenjen */
    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /** Prosečna ocena korisnika (null ako nema ocena) */
    public static function averageFor(int $userId): ?float
    {
        $avg = static::where('rated_user_id', $userId)->avg('stars');
        return $avg !== null ? round((float) $avg, 1) : null;
    }

    /** Broj ocena korisnika */
    public static function countFor(int $userId): int
    {
        return static::where('rated_user_id', $userId)->count();
    }
}
