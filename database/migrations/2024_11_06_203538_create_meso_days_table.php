<?php

use App\Models\Mesocycle;
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
        Schema::create('meso_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Mesocycle::class);
            $table->unsignedTinyInteger('week');
            $table->unsignedSmallInteger('weight')->nullable();
            $table->string('label');
            $table->unsignedTinyInteger('position');
            $table->tinyInteger('status');
            $table->json('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meso_days');
    }
};
