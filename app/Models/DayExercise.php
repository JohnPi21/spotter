<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayExercise extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function day(): BelongsTo
    {
        return $this->belongsTo(MesoDay::class, 'meso_day_id');
    }

    public function sets()
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function mesocycle(): Mesocycle|null
    {
        return $this?->day->mesocycle;
    }

    #[Scope]
    protected function ownedBy(Builder $query, int $userID)
    {
        return $query->whereHas('day.mesocycle', function ($q) use ($userID) {
            $q->where('user_id', $userID);
        });
    }
}
