<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject_name');
            $table->integer('tingkat');
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->decimal('nilai_quiz', 5, 2)->nullable();
            $table->decimal('nilai_harian', 5, 2)->nullable();
            $table->decimal('nilai_mid', 5, 2)->nullable();
            $table->decimal('nilai_final', 5, 2)->nullable();
            $table->timestamps();
            
            // A student should only have one score record per subject per teacher (per semester, but we ignore semester for now)
            $table->unique(['siswa_id', 'guru_id', 'subject_name'], 'student_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_scores');
    }
};
