<?php

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
        Schema::table('exercise_sets', function (Blueprint $table) {
            $table->after('target_reps', function (Blueprint $table) {
                $table->unsignedTinyInteger('min_reps')->nullable();
                $table->unsignedTinyInteger('max_reps')->nullable();
                $table->unsignedTinyInteger('min_rir')->nullable();
                $table->unsignedTinyInteger('max_rir')->nullable();
                $table->unsignedTinyInteger('percent_one_rep_max')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            //
        });
    }
};
