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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->dropColumn('sex');

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
