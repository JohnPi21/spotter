<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSet extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function dayExercise(): BelongsTo
    {
        return $this->belongsTo(DayExercise::class);
    }
}
