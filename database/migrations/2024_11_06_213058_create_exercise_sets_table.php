<?php

use App\Models\DayExercise;
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
        Schema::create('exercise_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DayExercise::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('reps')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->timestamp('finished_at')->nullable();
            // $table->boolean('bodyweight');
            // $table->boolean('assisted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_sets');
    }
};
