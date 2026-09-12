<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('payment_type', 30); // Pendaftaran, SPP, Asrama, Uang_Gedung
                $table->string('payment_status', 20)->default('Pending'); // Pending, Verified, Rejected
                $table->string('proof_file', 255)->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
