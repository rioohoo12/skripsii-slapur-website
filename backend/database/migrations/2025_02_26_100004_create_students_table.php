<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nis', 20)->unique()->nullable();
            $table->string('full_name', 100);
            $table->string('gender', 1); // L, P (sesuai frontend laki-laki/perempuan)

            $table->boolean('is_transfer_student')->default(false);
            $table->string('previous_school_name', 150)->nullable();

            $table->boolean('is_boarder')->default(false);
            $table->foreignId('assigned_room_id')->nullable()->constrained('dorm_rooms')->nullOnDelete();

            $table->boolean('has_paid_registration')->default(false);
            $table->boolean('has_uploaded_docs')->default(false);
            $table->boolean('has_chosen_dorm_type')->default(false);
            $table->boolean('has_chosen_room')->default(false);
            $table->boolean('has_picked_up_key')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
