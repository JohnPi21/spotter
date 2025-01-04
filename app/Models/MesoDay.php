<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MesoDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function mesocycle(): BelongsTo
    {
        return $this->belongsTo(Mesocycle::class);
    }

    public function exercise(): HasManyThrough
    {
        return $this->hasManyThrough(Exercise::class, DayExercise::class, 'meso_day_id', 'id', 'id', 'exercise_id');
    }
}
