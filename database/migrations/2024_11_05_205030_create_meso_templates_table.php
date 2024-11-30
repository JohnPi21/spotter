<?php

use App\Models\Mesocycle;
use App\Models\User;
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
        Schema::create('meso_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sex');
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Mesocycle::class);
            $table->tinyInteger('frequency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meso_templates');
    }
};
