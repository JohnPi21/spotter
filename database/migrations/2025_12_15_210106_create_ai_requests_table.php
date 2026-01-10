<?php

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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->string('meta_id', 64);
            $table->foreignIdFor(User::class);
            $table->string('agent', 32); // mesocycle
            $table->string('model', 32); // gpt-5-mini
            $table->string('provider', 32); // openai
            $table->string('prompt_version', 32); // 1.xx
            $table->string('system_prompt_version', 32); // 1.xx

            $table->json('user_input_json'); // user input preferences
            $table->json('context_json'); // exercise id's muscle groups etc
            $table->string('finish_reason')->nullable(); // present in response
            $table->integer('latency_ms'); // measured on request

            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->json('usage_json')->nullable(); // from response


            $table->json('output_json')->nullable(); // what ai responded -> registered only on failure
            $table->string('error_class')->nullable();
            $table->string('error_message')->nullable();
            $table->string('error_stage')->nullable();
            $table->string('status', 16);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
