<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mesocycle extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;
    public const MIN_WEEKS = 3;
    public const MAX_WEEKS = 12;

    protected $guarded = [];

    protected $appends = ['last_day'];

    public function days(): HasMany
    {
        return $this->hasMany(MesoDay::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastDay(): Attribute
    {
        return Attribute::make(get: function () {
            // If relation is loaded, use it
            if ($this->relationLoaded('days')) {
                return $this->days
                    ->firstWhere('finished_at', null)?->id
                    ?? $this->days->last()?->id;
            }

            // Otherwise, query directly
            return $this->days()
                ->whereNull('finished_at')
                ->orderBy('id')
                ->value('id')
                ?? $this->days()->latest('id')->value('id');
        });
    }

    #[Scope]
    protected function mine(Builder $query): void
    {
        $this->ownedBy($query, Auth::id());
    }

    #[Scope]
    protected function ownedBy(Builder $query, ?int $userID): void
    {
        $query->where('user_id', $userID);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', 1);
    }

    public static function userHasActiveMeso(int $userId): bool
    {
        return static::query()->ownedBy($userId)->active()->exists();
    }

    public static function weeksRange(): array
    {
        return range(self::MIN_WEEKS, self::MAX_WEEKS);
    }

    public function totalDays(): int
    {
        return $this->days_per_week * $this->weeks_duration;
    }
}
