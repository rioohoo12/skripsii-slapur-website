<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'guest_token')) {
                $table->string('guest_token')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('chat_sessions', 'current_flow')) {
                $table->string('current_flow')->default('default')->after('guest_token');
            }
            if (!Schema::hasColumn('chat_sessions', 'current_step')) {
                $table->string('current_step')->default('init')->after('current_flow');
            }
            if (!Schema::hasColumn('chat_sessions', 'slots')) {
                $table->json('slots')->nullable()->after('current_step');
            }
            
            // Registration data column kept for SQLite compatibility
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'confidence')) {
                $table->float('confidence')->nullable()->after('intent');
            }
            if (!Schema::hasColumn('chat_messages', 'entities')) {
                $table->json('entities')->nullable()->after('confidence');
            }
            if (!Schema::hasColumn('chat_messages', 'source')) {
                $table->string('source', 50)->default('nlp')->after('entities');
            }
            if (!Schema::hasColumn('chat_messages', 'latency_ms')) {
                $table->integer('latency_ms')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['confidence', 'entities', 'source', 'latency_ms']);
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['guest_token', 'current_flow', 'current_step', 'slots']);
            $table->json('registration_data')->nullable();
        });
    }
};
