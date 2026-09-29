<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create applicants table
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nomor_pendaftaran', 50)->unique();
            $table->string('nama_lengkap', 100);
            $table->integer('usia')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        // 2. Create fees table
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Create invoices table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained('fees')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->string('status', 20)->default('unpaid'); // unpaid, paid, expired
            $table->timestamps();
        });

        // 4. Modify payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('id')->constrained('invoices')->nullOnDelete();
            $table->string('midtrans_transaction_id')->nullable()->after('invoice_id');
            // 'payment_type' already exists, let's keep it.
        });

        // 5. Rename chatbot_ tables to match new architecture
        Schema::rename('chatbot_answers', 'knowledge_items');
        Schema::rename('chatbot_sessions', 'chat_sessions');
        Schema::rename('chatbot_conversations', 'chat_messages');

        // 6. Modify knowledge_items
        Schema::table('knowledge_items', function (Blueprint $table) {
            $table->string('question')->nullable()->after('topic');
            // 'response' already exists, we will use it as answer
        });

        // 7. Modify chat_sessions
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->foreignId('applicant_id')->nullable()->after('user_id')->constrained('applicants')->nullOnDelete();
            $table->string('status', 20)->default('active')->after('registration_data'); // active, handed_off, closed
        });

        // 8. Modify chat_messages
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('sender_type', 20)->nullable()->after('role'); // bot, user, staff
            $table->string('intent', 50)->nullable()->after('message');
        });

        // 9. Create handoffs table
        Schema::create('handoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // pending, accepted, resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handoffs');

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'intent']);
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
            $table->dropColumn(['applicant_id', 'status']);
        });

        Schema::table('knowledge_items', function (Blueprint $table) {
            $table->dropColumn(['question']);
        });

        Schema::rename('knowledge_items', 'chatbot_answers');
        Schema::rename('chat_sessions', 'chatbot_sessions');
        Schema::rename('chat_messages', 'chatbot_conversations');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['invoice_id', 'midtrans_transaction_id']);
        });

        Schema::dropIfExists('invoices');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('applicants');
    }
};
