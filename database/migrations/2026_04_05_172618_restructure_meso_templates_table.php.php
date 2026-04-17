<?php

use App\Models\MesoTemplate;
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
        Schema::table('ai_requests', function (Blueprint $table) {
            if (Schema::hasColumn('ai_requests', 'sex')) {
                $table->dropColumnIfExists('sex');
            }

            $table->json('schema');
            $table->boolean('ai_generated');
            $table->foreignIdFor(MesoTemplate::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
