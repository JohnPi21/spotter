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
        Schema::table('day_exercises', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_reps')->nullable();
            $table->unsignedSmallInteger('max_reps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_exercises', function (Blueprint $table) {});
    }
};
