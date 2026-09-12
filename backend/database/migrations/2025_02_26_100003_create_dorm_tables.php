<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dorm_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name', 50);
            $table->decimal('cost', 15, 2);
            $table->timestamps();
        });

        Schema::create('dorm_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('building_name', 100);
            $table->string('gender_allowance', 20); // L, P, Campur (SQLite no ENUM)
            $table->timestamps();
        });

        Schema::create('dorm_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('dorm_buildings')->cascadeOnDelete();
            $table->foreignId('dorm_type_id')->constrained('dorm_types')->cascadeOnDelete();
            $table->string('room_number', 20);
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('current_occupancy')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dorm_rooms');
        Schema::dropIfExists('dorm_buildings');
        Schema::dropIfExists('dorm_types');
    }
};
