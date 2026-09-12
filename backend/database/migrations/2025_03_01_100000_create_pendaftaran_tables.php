<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Profile pendaftaran (mengisi biodata + kelas yang didaftar)
        Schema::create('pendaftaran_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap', 100);
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->unsignedTinyInteger('kelas_yang_didaftar'); // 7,8,9 SMP atau 10,11,12 SMA
            $table->timestamps();
        });

        // Pembayaran pendaftaran (60%, verifikasi) → untuk Clearance Slip
        Schema::create('pendaftaran_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->decimal('nominal_60_percent', 15, 2)->default(0);
            $table->decimal('nominal_dibayar', 15, 2)->default(0);
            $table->string('status', 20)->default('menunggu'); // menunggu, terverifikasi
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // Kamar (kapasitas 4, untuk pilih kamar)
        Schema::create('pendaftaran_kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kamar', 20)->unique();
            $table->unsignedTinyInteger('kapasitas')->default(4);
            $table->unsignedTinyInteger('current_occupancy')->default(0);
            $table->timestamps();
        });

        // Pilihan kamar per user
        Schema::create('pendaftaran_room_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('pendaftaran_kamar_id')->constrained('pendaftaran_kamar')->cascadeOnDelete();
            $table->timestamps();
        });

        // Mata pelajaran per tingkat (SMP 7,8,9 dan SMA 10,11,12)
        Schema::create('mata_pelajaran_tingkat', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tingkat'); // 7,8,9,10,11,12
            $table->string('nama_pelajaran', 100);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
        Schema::table('mata_pelajaran_tingkat', function (Blueprint $table) {
            $table->index(['tingkat']);
        });

        // Dokumen siswa (Akte, KK, Pas foto, Raport) → untuk Administrasi Upload Dokumen
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis', 30); // akte_kelahiran, kk, pas_foto, raport
            $table->string('file_path', 500)->nullable();
            $table->string('status', 20)->default('pending'); // pending, terverifikasi
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
        Schema::table('student_documents', function (Blueprint $table) {
            $table->unique(['user_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('pendaftaran_room_selections');
        Schema::dropIfExists('mata_pelajaran_tingkat');
        Schema::dropIfExists('pendaftaran_kamar');
        Schema::dropIfExists('pendaftaran_payments');
        Schema::dropIfExists('pendaftaran_profiles');
    }
};
