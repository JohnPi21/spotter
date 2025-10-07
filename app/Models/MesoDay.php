<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class MesoDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'finished_at' => 'datetime',
        'week' => 'integer',
        'day_order' => 'integer',
    ];

    public function mesocycle(): BelongsTo
    {
        return $this->belongsTo(Mesocycle::class);
    }

    public function dayExercises(): HasMany
    {
        return $this->hasMany(DayExercise::class, 'meso_day_id', 'id');
    }

    #[Scope]
    protected function ownedBy(Builder $query, int $userID)
    {
        return $query->whereHas('mesocycle', fn($q) => $q->where('user_id', $userID));
    }

    public function ensureIsEditable(): void
    {
        if ($this->finished_at) {
            throw ValidationException::withMessages([
                'day_status' => 'This day is already completed and cannot be modified.',
            ]);
        }
    }

    public function isEditable(): bool
    {
        return is_null($this->finished_at);
    }

    public function canFinish(): bool
    {
        return ! $this->dayExercises()->whereHas('sets', fn(Builder $q) => $q->whereNull('finished_at'))->exists();
    }

    public function orderSiblings(): HasMany
    {
        return $this->hasMany(self::class, 'mesocycle_id', 'mesocycle_id')->whereKeyNot($this->getKey())->where('day_order', $this->day_order);
    }
}
