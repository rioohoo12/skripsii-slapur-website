<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom pendukung pada chat_sessions
        Schema::table('chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_sessions', 'role')) {
                $table->string('role', 30)->default('umum')->after('user_id');
            }
            if (!Schema::hasColumn('chat_sessions', 'started_at')) {
                $table->timestamp('started_at')->useCurrent()->after('status');
            }
        });

        // 2. Tambah kolom pendukung pada chat_messages
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'pesan')) {
                $table->text('pesan')->nullable()->after('message');
            }
            if (!Schema::hasColumn('chat_messages', 'respons')) {
                $table->text('respons')->nullable()->after('pesan');
            }
            if (!Schema::hasColumn('chat_messages', 'sumber')) {
                $table->string('sumber', 50)->default('local')->after('source');
            }
            if (!Schema::hasColumn('chat_messages', 'tokens')) {
                $table->integer('tokens')->default(0)->after('sumber');
            }
        });

        // 3. Tabel knowledge_base
        if (!Schema::hasTable('knowledge_base')) {
            Schema::create('knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->string('kategori', 50)->index();
                $table->text('pertanyaan');
                $table->text('jawaban');
                $table->string('role_akses', 30)->default('semua')->index(); // semua, murid, guru, staff, admin
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed awal data knowledge_base
            DB::table('knowledge_base')->insert([
                [
                    'kategori' => 'pendaftaran',
                    'pertanyaan' => 'Apa saja syarat pendaftaran siswa baru?',
                    'jawaban' => 'Syarat pendaftaran: 1) Pasfoto 3x4 (3 lembar), 2) Fotokopi Akte Kelahiran & KK, 3) Fotokopi Ijazah/SKL dilegalisir, 4) Surat Keterangan Sehat.',
                    'role_akses' => 'semua',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kategori' => 'biaya',
                    'pertanyaan' => 'Berapa biaya pendaftaran dan SPP sekolah?',
                    'jawaban' => 'Rincian biaya: Formulir Pendaftaran Rp 250.000, Uang Pangkal Rp 5.000.000, SPP Bulanan Rp 750.000, dan Biaya Asrama+Makan Rp 1.200.000/bulan.',
                    'role_akses' => 'semua',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kategori' => 'asrama',
                    'pertanyaan' => 'Bagaimana aturan dan kegiatan di asrama?',
                    'jawaban' => 'Aturan asrama: Wajib shalat berjamaah, jam belajar malam pukul 19.30 - 21.00 WIB. Jam berkunjung orang tua setiap hari Minggu pukul 09.00 - 16.00 WIB.',
                    'role_akses' => 'semua',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kategori' => 'guru',
                    'pertanyaan' => 'Bagaimana cara input nilai dan absensi siswa?',
                    'jawaban' => 'Panduan Guru: Masuk ke Menu Guru -> Pilih ' . "'Absensi Guru'" . ' atau ' . "'Nilai Siswa'" . ' -> Pilih Kelas -> Isi data dan klik Simpan.',
                    'role_akses' => 'guru',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kategori' => 'staff',
                    'pertanyaan' => 'Bagaimana verifikasi pembayaran pendaftar?',
                    'jawaban' => 'Panduan Staff Administrasi: Buka Menu Administrasi -> Pilih Data Pendaftaran -> Cek Bukti Transfer -> Klik Konfirmasi / Verifikasi.',
                    'role_akses' => 'staff',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kategori' => 'admin',
                    'pertanyaan' => 'Bagaimana kelola akun user dan role sistem?',
                    'jawaban' => 'Panduan Admin: Buka Menu Dashboard Admin -> Kelola User -> Pilih Role (Murid/Guru/Staff/Admin) -> Update atau Tambah User baru.',
                    'role_akses' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
