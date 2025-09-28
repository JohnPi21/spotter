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
            $table->decimal('weight', 7, 3)->nullable();
            $table->unsignedTinyInteger('reps')->nullable();
            $table->decimal('target_weight', 7, 3)->nullable();
            $table->unsignedTinyInteger('target_reps')->nullable();
            $table->timestamp('finished_at')->nullable();
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
