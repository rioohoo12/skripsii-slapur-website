<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name', 50)->unique();
            $table->text('description')->nullable();

            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_settings')->default(false);
            $table->boolean('can_input_grades')->default(false);
            $table->boolean('can_view_grades')->default(false);
            $table->boolean('can_input_attendance')->default(false);
            $table->boolean('can_manage_classes')->default(false);
            $table->boolean('can_scan_meal_card')->default(false);
            $table->boolean('can_manage_cafeteria_menu')->default(false);
            $table->boolean('can_manage_dorm_rooms')->default(false);
            $table->boolean('can_assign_student_room')->default(false);
            $table->boolean('can_manage_keys')->default(false);
            $table->boolean('can_verify_payments')->default(false);
            $table->boolean('can_verify_documents')->default(false);
            $table->boolean('can_access_student_dashboard')->default(false);
            $table->boolean('can_choose_dorm_room')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
