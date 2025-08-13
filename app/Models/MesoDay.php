<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\ValidationException;

class MesoDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'finished_at' => 'datetime',
    ];

    public function mesocycle(): BelongsTo
    {
        return $this->belongsTo(Mesocycle::class);
    }

    public function dayExercises(): HasMany
    {
        return $this->hasMany(DayExercise::class, 'meso_day_id', 'id');
    }

    // public function exercises(): HasManyThrough
    // {
    //     return $this->hasManyThrough(Exercise::class, DayExercise::class, 'meso_day_id', 'id', 'id', 'exercise_id');
    // }

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
        return ! ((bool)$this->finished_at);
    }
}
