<?php

use App\Models\AiRequest;
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
        Schema::table('meso_templates', function (Blueprint $table) {
            if (Schema::hasColumn('meso_templates', 'sex')) {
                $table->dropColumn('sex');
            }

            $table->after('frequency', function () use ($table) {
                $table->json('schema');
                $table->boolean('ai_generated')->default(false);
                $table->foreignIdFor(AiRequest::class)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
