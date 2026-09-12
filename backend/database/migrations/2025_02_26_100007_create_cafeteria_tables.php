<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cafeteria_menus', function (Blueprint $table) {
            $table->id();
            $table->date('date_served');
            $table->string('meal_time', 20); // Pagi, Siang, Sore
            $table->text('menu_details');
            $table->timestamps();
            $table->unique(['date_served', 'meal_time']);
        });

        Schema::create('cafeteria_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('date_consumed');
            $table->string('meal_time', 20);
            $table->unsignedInteger('eating_number');
            $table->timestamp('scanned_at')->useCurrent();
            $table->unique(['student_id', 'date_consumed', 'meal_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafeteria_logs');
        Schema::dropIfExists('cafeteria_menus');
    }
};
