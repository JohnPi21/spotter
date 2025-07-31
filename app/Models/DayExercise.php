<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Scope;

class DayExercise extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function day()
    {
        return $this->belongsTo(MesoDay::class, 'meso_day_id');
    }

    public function sets()
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function exercise(): HasOne
    {
        return $this->hasOne(Exercise::class, 'id', 'exercise_id');
    }

    public function mesocycle(): Mesocycle|null
    {
        return $this?->day->mesocycle;
    }

    #[Scope]
    protected function forUser(Builder $query, int $userID)
    {
        return $query->whereHas('day.mesocycle', function ($q) use ($userID) {
            $q->where('user_id', $userID);
        });
    }
}
