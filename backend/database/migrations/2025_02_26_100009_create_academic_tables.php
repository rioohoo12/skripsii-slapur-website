<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year_name', 20); // 2024/2025
            $table->string('semester', 10); // Ganjil, Genap
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name', 100);
            $table->string('subject_code', 20)->unique()->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('class_name', 20); // X-IPA-1
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('day_of_week', 20); // Senin, Selasa, ...
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20); // Hadir, Izin, Sakit, Alpha
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('grade_type', 20); // Tugas, UH, UTS, UAS
            $table->decimal('score', 5, 2);
            $table->text('teacher_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('student_classes');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_years');
    }
};
