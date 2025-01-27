<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DayExercise extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sets()
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function exercise(): HasOne
    {
        return $this->hasOne(Exercise::class, 'id', 'exercise_id');
    }
}
