<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesocycles', function (Blueprint $table) {
            $table->json('schema')->nullable();
            $table->json('planned_stats')->nullable();
        });

        Schema::table('meso_templates', function (Blueprint $table) {
            $table->json('planned_stats')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mesocycles', function (Blueprint $table) {
            $table->dropColumn(['schema', 'planned_stats']);
        });

        Schema::table('meso_templates', function (Blueprint $table) {
            $table->dropColumn('planned_stats');
        });
    }
};
