<?php

use App\Models\MesoDay;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('day_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MesoDay::class);
            $table->foreignIdFor(Exercise::class);
            $table->unsignedTinyInteger('position');
            // $table->foreignIdFor(ExerciseSet::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_exercises');
    }
};
