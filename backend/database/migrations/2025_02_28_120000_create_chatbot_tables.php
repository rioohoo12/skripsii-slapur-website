<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_answers', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 80)->index();
            $table->string('step_key', 80)->nullable()->index();
            $table->json('trigger_keywords');
            $table->text('response');
            $table->string('next_step_key', 80)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_initial')->default(false);
            $table->timestamps();
        });

        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('current_step_key', 80)->nullable();
            $table->json('registration_data')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->index();
            $table->enum('role', ['user', 'assistant']);
            $table->text('message');
            $table->unsignedBigInteger('answer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
        Schema::dropIfExists('chatbot_sessions');
        Schema::dropIfExists('chatbot_answers');
    }
};
