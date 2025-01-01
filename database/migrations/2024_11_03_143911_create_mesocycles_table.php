<?php

use App\Models\MesoTemplate;
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
        Schema::create('mesocycles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('unit', 4);
            $table->unsignedTinyInteger('days');
            $table->unsignedTinyInteger('weeks');
            $table->foreignIdFor(User::class);
            $table->json('notes')->nullable();
            $table->unsignedTinyInteger('status');
            $table->foreignIdFor(MesoTemplate::class)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesocycles');
    }
};
