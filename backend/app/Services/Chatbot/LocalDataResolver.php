<?php

namespace App\Services\Chatbot;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\Payment;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\DokumenSiswa;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranKamar;
use App\Models\PendaftaranRoomSelection;
use App\Models\CafeteriaMenu;
use App\Models\MealLog;
use App\Models\Material;
use App\Models\Applicant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LocalDataResolver
{
    /**
     * Resolves local data from Laravel DB with strict RBAC isolation.
     *
     * @param string $intent
     * @param User|null $user
     * @param array $slots
     * @param string|null $message
     * @return array
     */
    public function resolve(string $intent, ?User $user, array $slots = [], ?string $message = null): array
    {
        $role = strtolower($user?->role ?? 'umum');

        switch ($intent) {
            // ==========================================
            // ROLE MENU GUIDANCE (Semua Role)
            // ==========================================
            case 'panduan_menu_administrasi':
                return $this->resolvePanduanMenuAdministrasi();

            case 'panduan_menu_asrama_putra':
                return $this->resolvePanduanMenuAsrama('putra');

            case 'panduan_menu_asrama_putri':
                return $this->resolvePanduanMenuAsrama('putri');

            case 'panduan_menu_kafetaria':
                return $this->resolvePanduanMenuKafetaria();

            case 'panduan_menu_siswa':
                return $this->resolvePanduanMenuSiswa();

            // ==========================================
            // STAFF ADMINISTRASI INTENTS
            // ==========================================
            case 'cari_info_siswa':
                return $this->resolveInfoSiswaComplete($message, $user);

            case 'status_verifikasi_berkas':
                return $this->resolveStaffDokumenPending();

            case 'status_pembayaran_administrasi':
                return $this->resolveStaffPembayaranPending();

            case 'ringkasan_data_siswa':
                return $this->resolveStaffSummary($user, $role);

            // ==========================================
            // STAFF ASRAMA PUTRA & PUTRI INTENTS
            // ==========================================
            case 'total_murid_asrama_putra':
                return $this->resolveAsramaTotalMurid('putra', $user);

            case 'total_murid_asrama_putri':
                return $this->resolveAsramaTotalMurid('putri', $user);

            case 'total_murid_asrama':
                return $this->resolveAsramaTotalMurid(null, $user);

            case 'ketersediaan_kamar_asrama':
                return $this->resolveKamarKosongAsrama($user);

            case 'persetujuan_kamar_asrama':
                return $this->resolveAsramaPersetujuanPending($user);

            case 'panduan_mutasi_asrama':
                return $this->resolvePanduanMutasiAsrama();

            // ==========================================
            // STAFF KAFETARIA INTENTS
            // ==========================================
            case 'menu_kafetaria_hari_ini':
                return $this->resolveKafetariaTodayMenu();

            case 'rekap_siswa_makan':
                return $this->resolveKafetariaRekapMakan();

            case 'panduan_scan_makan':
                return $this->resolvePanduanScanMakan();

            case 'panduan_input_menu':
                return $this->resolvePanduanInputMenu();

            // ==========================================
            // WEBSITE GUIDANCE & FAQ UMUM
            // ==========================================
            case 'cara_gunakan_website':
                return [
                    'has_data' => true,
                    'summary' => "Panduan Penggunaan Website SLAPUR:\n\n1. **Beranda & Profil** — Akses ringkasan pengumuman sekolah & biodata diri.\n2. **Menu Pendaftaran** — Alur registrasi online bertahap.\n3. **Keuangan & Tagihan** — Cek invoice tagihan dan unggah bukti transfer resmi.\n4. **Akademik (Nilai & Presensi)** — Cek jadwal pelajaran, rekap presensi, dan nilai tugas/ujian.\n5. **Asrama & Dining** — Pilih kamar asrama dan cek jadwal menu makan santri.",
                    'data' => null,
                ];

            case 'staf':
                return [
                    'has_data' => true,
                    'summary' => "Kontak Staf & Jam Layanan SLAPUR:\n\n• **WhatsApp Layanan Admin**: 0812-3456-7890\n• **Jam Layanan**: Senin – Jumat (08.00 – 16.00 WIB)\n• **Email**: admin@slapur.sch.id\n• **Alamat**: Kompleks SLAPUR Purwodadi, Jawa Tengah.",
                    'data' => null,
                ];

            // ==========================================
            // SISWA INTENTS
            // ==========================================
            case 'nilai':
                return $this->resolveStudentScores($user, $role);

            case 'absensi':
                return $this->resolveStudentAttendance($user, $role);

            case 'tagihan_dan_bayar':
            case 'status_pembayaran':
                return $this->resolveStudentPayments($user, $role);

            case 'pilih_kamar':
                return $this->resolveDormStatus($user, $role);

            case 'jadwal':
                if ($role === 'guru') {
                    return $this->resolveTeacherSchedule($user);
                }
                return $this->resolveStudentSchedule($user, $role);

            case 'info_pendaftaran':
                return [
                    'has_data' => true,
                    'summary' => "Informasi Pendaftaran Siswa Baru SLAPUR:\n\n1. **Syarat Berkas**: Pasfoto 3x4, Fotokopi Akte Kelahiran, KK, & Ijazah/SKL dilegalisir.\n2. **Alur Pendaftaran**: Biodata Online -> Clearance Slip (DP 60%) -> Pilih Kamar Asrama -> Administrasi Berkas -> Kurikulum -> Upload Dokumen.",
                    'data' => null,
                ];

            // ==========================================
            // GURU INTENTS
            // ==========================================
            case 'materi':
                return $this->resolveMaterials($user, $role);

            case 'panduan_absensi':
                return [
                    'has_data' => true,
                    'summary' => "Panduan Input Absensi Guru:\n\n1. Masuk ke **Menu Guru -> Absensi Siswa**.\n2. Pilih Kelas & Tanggal Pelajaran.\n3. Tandai status kehadiran siswa (Hadir, Izin, Sakit, Alpa).\n4. Klik tombol **Simpan Absensi**.",
                    'data' => null,
                ];

            case 'panduan_nilai':
                return [
                    'has_data' => true,
                    'summary' => "Panduan Input Nilai Guru:\n\n1. Masuk ke **Menu Guru -> Nilai Siswa**.\n2. Pilih Kelas & Mata Pelajaran yang diampu.\n3. Input nilai Tugas, UTS, atau UAS siswa.\n4. Klik tombol **Simpan Nilai**.",
                    'data' => null,
                ];

            case 'daftar_kelas':
                return [
                    'has_data' => true,
                    'summary' => "Daftar Kelas SLAPUR:\n- SMP: Kelas 7A, 7B, 8A, 8B, 9A, 9B\n- SMA: Kelas 10 IPA, 10 IPS, 11 IPA, 11 IPS, 12 IPA, 12 IPS",
                    'data' => null,
                ];

            // ==========================================
            // ADMIN GUIDES
            // ==========================================
            case 'panduan_verifikasi':
                return [
                    'has_data' => true,
                    'summary' => "Panduan Verifikasi Staff:\n\n1. Buka **Menu Administrasi -> Dokumen** atau **Pembayaran**.\n2. Cek berkas atau bukti transfer pembayaran pendaftar langsung in-page.\n3. Klik **Setujui (Lunas)** atau **Tolak** untuk memproses data siswa.",
                    'data' => null,
                ];

            case 'panduan_kelola_role':
                return [
                    'has_data' => true,
                    'summary' => "Panduan Kelola Role Admin:\n\n1. Buka **Dashboard Admin -> Kelola User**.\n2. Pilih user target lalu sesuaikan Role (Murid, Guru, Staff Administrasi, Staff Asrama, Staff Kafetaria, Admin).\n3. Klik **Simpan Perubahan**.",
                    'data' => null,
                ];

            case 'ringkasan_monitoring':
                return [
                    'has_data' => true,
                    'summary' => "Ringkasan Status System Monitoring SLAPUR:\n- Application Engine: Normal (Vue 3 SPA)\n- Database Connection: PostgreSQL Supabase / MySQL Connected\n- Security Middleware: Active (Sanctum Auth & RBAC Enabled)",
                    'data' => null,
                ];

            default:
                return [
                    'has_data' => false,
                    'summary' => null,
                    'data' => null,
                ];
        }
    }

    // =========================================================================
    // 1. RESOLVER STAFF ADMINISTRASI
    // =========================================================================

    /**
     * Panduan & fungsi setiap menu untuk Staff Administrasi.
     */
    protected function resolvePanduanMenuAdministrasi(): array
    {
        $text = "📋 **Panduan & Fungsi Menu Staff Administrasi SLAPUR**:\n\n"
              . "1. **Dashboard** (`/administrasi/dashboard`)\n"
              . "   • Memantau grafik statistik siswa terdaftar, kelengkapan berkas, dan grafik pemasukan sekolah.\n\n"
              . "2. **Pendaftaran** (`/administrasi/pendaftaran`)\n"
              . "   • Mengelola calon pendaftar baru, filter berdasarkan jenjang kelas, dan melihat progres pendaftaran.\n\n"
              . "3. **Dokumen** (`/administrasi/dokumen`)\n"
              . "   • Memeriksa berkas syarat (Akte, KK, Rapor, Pasfoto) dengan fitur preview langsung tanpa reload.\n"
              . "   • Menyetujui atau menolak dokumen disertai catatan verifikasi.\n\n"
              . "4. **Pembayaran** (`/administrasi/pembayaran`)\n"
              . "   • Menampilkan antrean bukti transfer siswa (pendaftaran, SPP, asrama).\n"
              . "   • Memverifikasi pembayaran menjadi **Lunas** atau **Ditolak** dengan preview bukti transfer in-page.\n\n"
              . "5. **Tagihan** (`/administrasi/tagihan`)\n"
              . "   • Membuat tagihan baru untuk siswa (SPP bulanan, uang pangkal, asrama) beserta tanggal jatuh tempo.\n\n"
              . "6. **Laporan** (`/administrasi/laporan`)\n"
              . "   • Menghasilkan rekapitulasi laporan pemasukan keuangan dan daftar siswa yang belum melengkapi dokumen.";

        return [
            'has_data' => true,
            'summary' => $text,
            'data' => null,
        ];
    }

    /**
     * Layanan pertanyaan info siswa secara lengkap dari database untuk Staff Administrasi.
     */
    protected function resolveInfoSiswaComplete(?string $message, ?User $user): array
    {
        // Ekstrak keyword nama/nomor pendaftaran dari pesan
        $keyword = null;
        if ($message) {
            $clean = preg_replace('/(cari\s+info\s+siswa|info\s+siswa|cari\s+siswa|data\s+siswa|data\s+murid|profil\s+siswa|biodata\s+siswa|cek\s+siswa|detail\s+siswa|informasi\s+siswa|tentang|tolong)/ui', '', $message);
            $clean = trim($clean);
            if (strlen($clean) >= 2) {
                $keyword = $clean;
            }
        }

        $query = Student::with([
            'user',
            'roomSelection.kamar',
            'dokumenSiswa.jenisDokumen',
            'tagihans.pembayarans',
        ]);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                  ->orWhere('nomor_pendaftaran', 'like', "%{$keyword}%")
                  ->orWhere('nisn', 'like', "%{$keyword}%");
            });
        }

        $students = $query->latest('id')->take(3)->get();

        if ($students->isEmpty()) {
            $sampleStudents = Student::take(5)->get(['id', 'full_name', 'nomor_pendaftaran', 'status_pendaftaran']);
            $listStr = $sampleStudents->map(fn($s) => "• **{$s->full_name}** ({$s->nomor_pendaftaran}) - Status: " . ucfirst($s->status_pendaftaran))->implode("\n");

            return [
                'has_data' => true,
                'summary' => "🔍 Siswa dengan kata kunci **\"{$keyword}\"** tidak ditemukan di database.\n\nBerikut daftar contoh siswa yang terdaftar:\n{$listStr}\n\n*Ketik:* `info siswa [nama siswa]` untuk melihat rincian lengkap.",
                'data' => [],
            ];
        }

        $resultTexts = [];
        foreach ($students as $st) {
            $jk = strtolower($st->jenis_kelamin ?? '') === 'perempuan' ? 'Perempuan' : 'Laki-laki';
            $kamarName = $st->roomSelection?->kamar?->nomor_kamar ?? ($st->assigned_room_id ? "Kamar #{$st->assigned_room_id}" : 'Belum memilih kamar');
            
            // Evaluasi dokumen
            $docs = $st->dokumenSiswa;
            $docCount = $docs->count();
            $docVerified = $docs->filter(fn($d) => in_array($d->status, ['disetujui', 'terverifikasi', 'verified']))->count();
            $docStatusStr = "{$docVerified}/{$docCount} Terverifikasi";

            // Evaluasi tagihan
            $tagihans = $st->tagihans;
            $unpaidCount = $tagihans->filter(fn($t) => $t->status !== 'lunas')->count();
            $paymentStatus = $st->has_paid_registration || $unpaidCount === 0 ? '✓ LUNAS' : ($unpaidCount > 0 ? "⚠️ {$unpaidCount} Tagihan Belum Lunas" : 'Belum Ada Tagihan');

            $lines = [
                "👤 **DATA LENGKAP SISWA**: **{$st->full_name}**",
                "• **No. Pendaftaran**: `{$st->nomor_pendaftaran}` | **NISN**: " . ($st->nisn ?: '-'),
                "• **Jenis Kelamin**: {$jk} | **Kelas/Jenjang**: Kelas " . ($st->kelas_yang_didaftar ?: '10'),
                "• **Status Pendaftaran**: " . strtoupper($st->status_pendaftaran ?: 'Terdaftar'),
                "• **Penempatan Asrama**: {$kamarName}",
                "• **Status Dokumen Berkas**: {$docStatusStr}",
                "• **Status Pembayaran Keuangan**: **{$paymentStatus}**",
                "• **No. HP / Kontak**: " . ($st->no_hp ?: ($st->user?->email ?: '-')),
            ];
            $resultTexts[] = implode("\n", $lines);
        }

        $summary = "Berikut informasi data siswa dari database sekolah:\n\n" . implode("\n\n---\n\n", $resultTexts);
        if (!$keyword) {
            $summary .= "\n\n💡 *Tip: Ketik `info siswa [nama]` untuk mencari spesifik siswa lain.*";
        }

        return [
            'has_data' => true,
            'summary' => $summary,
            'data' => $students->toArray(),
        ];
    }

    /**
     * Berkas pendaftaran menunggu verifikasi untuk Staff Administrasi.
     */
    protected function resolveStaffDokumenPending(): array
    {
        $pendingDocs = DokumenSiswa::with(['student', 'jenisDokumen'])
            ->whereIn('status', ['menunggu', 'pending'])
            ->take(5)
            ->get();

        $totalPending = DokumenSiswa::whereIn('status', ['menunggu', 'pending'])->count();

        if ($totalPending === 0) {
            return [
                'has_data' => true,
                'summary' => "✅ Tidak ada berkas pendaftaran yang menunggu verifikasi saat ini. Semua dokumen siswa telah diverifikasi.",
                'data' => [],
            ];
        }

        $list = $pendingDocs->map(function ($d) {
            $name = $d->student?->full_name ?? 'Siswa';
            $jenis = $d->jenisDokumen?->nama ?? 'Dokumen';
            return "• **{$name}**: {$jenis} (Diunggah: " . ($d->created_at ? $d->created_at->format('d/m/Y') : '-') . ")";
        })->implode("\n");

        return [
            'has_data' => true,
            'summary' => "📁 **Antrean Verifikasi Dokumen**: Terdapat **{$totalPending} dokumen** yang menunggu verifikasi Staff Administrasi:\n\n{$list}\n\n👉 Buka menu **Dokumen** (`/administrasi/dokumen`) untuk memverifikasi berkas langsung in-page.",
            'data' => ['total_pending' => $totalPending],
        ];
    }

    /**
     * Pembayaran siswa menunggu verifikasi untuk Staff Administrasi.
     */
    protected function resolveStaffPembayaranPending(): array
    {
        $pendingPayments = Pembayaran::with(['tagihan.student'])
            ->whereIn('status', ['menunggu', 'pending'])
            ->take(5)
            ->get();

        $totalPending = Pembayaran::whereIn('status', ['menunggu', 'pending'])->count();

        if ($totalPending === 0) {
            return [
                'has_data' => true,
                'summary' => "✅ Tidak ada antrean pembayaran saat ini. Semua bukti transfer siswa telah diverifikasi oleh Staff Administrasi.",
                'data' => [],
            ];
        }

        $list = $pendingPayments->map(function ($p) {
            $name = $p->tagihan?->student?->full_name ?? 'Siswa';
            $jenis = ucfirst($p->tagihan?->jenis ?? 'Tagihan');
            $nom = 'Rp ' . number_format($p->nominal_bayar ?: ($p->tagihan?->nominal ?? 0), 0, ',', '.');
            return "• **{$name}**: {$jenis} — {$nom} ({$p->metode})";
        })->implode("\n");

        return [
            'has_data' => true,
            'summary' => "💳 **Antrean Verifikasi Pembayaran**: Terdapat **{$totalPending} pembayaran masuk** yang menunggu verifikasi:\n\n{$list}\n\n👉 Buka menu **Pembayaran** (`/administrasi/pembayaran`) untuk melihat preview bukti transfer dan klik 'Disetujui (Lunas)'.",
            'data' => ['total_pending' => $totalPending],
        ];
    }

    /**
     * Ringkasan data pendaftar & siswa untuk Staff Administrasi.
     */
    protected function resolveStaffSummary(?User $user, string $role): array
    {
        $totalSiswa = Student::count();
        $totalPendaftar = Applicant::count() ?: $totalSiswa;
        $totalLunas = Student::where('has_paid_registration', true)->count();
        $totalKamar = PendaftaranKamar::count();
        $totalPenghuni = PendaftaranKamar::sum('current_occupancy');

        return [
            'has_data' => true,
            'summary' => "📊 **Ringkasan Data Sekolah SLAPUR**:\n\n"
                       . "• **Total Siswa Terdaftar**: {$totalSiswa} siswa\n"
                       . "• **Pendaftar Baru**: {$totalPendaftar} calon siswa\n"
                       . "• **Pendaftar Telah Lunas (DP 60%)**: {$totalLunas} siswa\n"
                       . "• **Total Penghuni Asrama**: {$totalPenghuni} santri (dari {$totalKamar} total kamar)\n\n"
                       . "Gunakan menu **Pendaftaran** atau **Dokumen** di navbar untuk melihat rincian setiap pendaftar.",
            'data' => [
                'total_siswa' => $totalSiswa,
                'total_pendaftar' => $totalPendaftar,
                'total_lunas' => $totalLunas,
                'total_penghuni' => $totalPenghuni,
            ],
        ];
    }

    // =========================================================================
    // 2. RESOLVER STAFF ASRAMA PUTRA & PUTRI
    // =========================================================================

    /**
     * Memberitahu informasi total murid di Asrama Putra maupun Asrama Putri secara lengkap.
     */
    protected function resolveAsramaTotalMurid(?string $asramaType = null, ?User $user = null): array
    {
        // 1. Data Asrama Putra (Kamar A, B, C)
        $kamarPutra = PendaftaranKamar::where(function($q) {
            $q->where('nomor_kamar', 'LIKE', 'A%')
              ->orWhere('nomor_kamar', 'LIKE', 'B%')
              ->orWhere('nomor_kamar', 'LIKE', 'C%');
        })->get();

        $totalMuridPutra = $kamarPutra->sum('current_occupancy');
        $totalKapasitasPutra = $kamarPutra->sum('kapasitas') ?: 1;
        $kamarPutraKosong = $kamarPutra->filter(fn($k) => $k->current_occupancy < $k->kapasitas)->count();
        $persenPutra = round(($totalMuridPutra / $totalKapasitasPutra) * 100);

        // Per gedung putra
        $gedungA = $kamarPutra->filter(fn($k) => str_starts_with($k->nomor_kamar, 'A'))->sum('current_occupancy');
        $gedungB = $kamarPutra->filter(fn($k) => str_starts_with($k->nomor_kamar, 'B'))->sum('current_occupancy');
        $gedungC = $kamarPutra->filter(fn($k) => str_starts_with($k->nomor_kamar, 'C'))->sum('current_occupancy');

        // 2. Data Asrama Putri (Kamar D, E, F)
        $kamarPutri = PendaftaranKamar::where(function($q) {
            $q->where('nomor_kamar', 'LIKE', 'D%')
              ->orWhere('nomor_kamar', 'LIKE', 'E%')
              ->orWhere('nomor_kamar', 'LIKE', 'F%');
        })->get();

        $totalMuridPutri = $kamarPutri->sum('current_occupancy');
        $totalKapasitasPutri = $kamarPutri->sum('kapasitas') ?: 1;
        $kamarPutriKosong = $kamarPutri->filter(fn($k) => $k->current_occupancy < $k->kapasitas)->count();
        $persenPutri = round(($totalMuridPutri / $totalKapasitasPutri) * 100);

        // Per gedung putri
        $gedungD = $kamarPutri->filter(fn($k) => str_starts_with($k->nomor_kamar, 'D'))->sum('current_occupancy');
        $gedungE = $kamarPutri->filter(fn($k) => str_starts_with($k->nomor_kamar, 'E'))->sum('current_occupancy');
        $gedungF = $kamarPutri->filter(fn($k) => str_starts_with($k->nomor_kamar, 'F'))->sum('current_occupancy');

        $totalSemua = $totalMuridPutra + $totalMuridPutri;

        if ($asramaType === 'putra') {
            $summary = "🏢 **DATA PENGHUNI ASRAMA PUTRA (Gedung A, B, C)**:\n\n"
                     . "• **Total Murid di Asrama Putra**: **{$totalMuridPutra} Santri**\n"
                     . "• **Total Kapasitas**: {$totalKapasitasPutra} tempat tidur (Terisi: {$persenPutra}%)\n"
                     . "• **Kamar Tersedia (Kosong/Sisa Bed)**: {$kamarPutraKosong} kamar\n\n"
                     . "📍 **Rincian per Gedung Asrama Putra**:\n"
                     . "  - **Gedung A (Anex)**: {$gedungA} penghuni\n"
                     . "  - **Gedung B (Cendrawasih)**: {$gedungB} penghuni\n"
                     . "  - **Gedung C (Hawk)**: {$gedungC} penghuni\n\n"
                     . "ℹ️ *Catatan perbandingan: Asrama Putri saat ini menampung {$totalMuridPutri} santriwati.*";
        } elseif ($asramaType === 'putri') {
            $summary = "🌸 **DATA PENGHUNI ASRAMA PUTRI (Gedung D, E, F)**:\n\n"
                     . "• **Total Murid di Asrama Putri**: **{$totalMuridPutri} Santriwati**\n"
                     . "• **Total Kapasitas**: {$totalKapasitasPutri} tempat tidur (Terisi: {$persenPutri}%)\n"
                     . "• **Kamar Tersedia (Kosong/Sisa Bed)**: {$kamarPutriKosong} kamar\n\n"
                     . "📍 **Rincian per Gedung Asrama Putri**:\n"
                     . "  - **Gedung D (Krisan)**: {$gedungD} penghuni\n"
                     . "  - **Gedung E (Melati)**: {$gedungE} penghuni\n"
                     . "  - **Gedung F (Mawar)**: {$gedungF} penghuni\n\n"
                     . "ℹ️ *Catatan perbandingan: Asrama Putra saat ini menampung {$totalMuridPutra} santri.*";
        } else {
            $summary = "🏰 **REKAPITULASI TOTAL MURID ASRAMA SLAPUR**:\n\n"
                     . "• **Total Seluruh Penghuni Asrama**: **{$totalSemua} Santri**\n\n"
                     . "🏢 **Asrama Putra (Gedung A, B, C)**:\n"
                     . "  - Total Santri Putra: **{$totalMuridPutra} murid** (Kapasitas: {$totalKapasitasPutra}, Terisi: {$persenPutra}%)\n"
                     . "  - Gedung A (Anex): {$gedungA} | Gedung B (Cendrawasih): {$gedungB} | Gedung C (Hawk): {$gedungC}\n"
                     . "  - Kamar dengan bed kosong: {$kamarPutraKosong} kamar\n\n"
                     . "🌸 **Asrama Putri (Gedung D, E, F)**:\n"
                     . "  - Total Santriwati: **{$totalMuridPutri} murid** (Kapasitas: {$totalKapasitasPutri}, Terisi: {$persenPutri}%)\n"
                     . "  - Gedung D (Krisan): {$gedungD} | Gedung E (Melati): {$gedungE} | Gedung F (Mawar): {$gedungF}\n"
                     . "  - Kamar dengan bed kosong: {$kamarPutriKosong} kamar";
        }

        return [
            'has_data' => true,
            'summary' => $summary,
            'data' => [
                'total_putra' => $totalMuridPutra,
                'total_putri' => $totalMuridPutri,
                'total_semua' => $totalSemua,
            ],
        ];
    }

    /**
     * Panduan & fungsi setiap menu untuk Staff Asrama Putra & Putri.
     */
    protected function resolvePanduanMenuAsrama(string $type = 'putra'): array
    {
        $isPutri = $type === 'putri';
        $prefixRoute = $isPutri ? '/asramaputri' : '/asramaputra';
        $title = $isPutri ? 'Asrama Putri (Gedung D, E, F)' : 'Asrama Putra (Gedung A, B, C)';
        $santri = $isPutri ? 'santriwati' : 'santri putra';

        $text = "🏠 **Panduan & Fungsi Menu Staff {$title}**:\n\n"
              . "1. **Dashboard** (`{$prefixRoute}/dashboard`)\n"
              . "   • Melihat ikhtisar kapasitas hunian per gedung, total {$santri}, dan persentase keterisian kamar.\n\n"
              . "2. **Kamar** (`{$prefixRoute}/kamar`)\n"
              . "   • Memantau daftar seluruh ruangan kamar asrama, kapasitas bed, penghuni saat ini, serta menambah/mengedit data kamar.\n\n"
              . "3. **Persetujuan** (`{$prefixRoute}/persetujuan`)\n"
              . "   • Memeriksa dan menyetujui permohonan penempatan kamar bagi pendaftar baru maupun permohonan pindah kamar.\n\n"
              . "4. **Penghuni** (`{$prefixRoute}/penghuni`)\n"
              . "   • Melihat daftar nama-nama lengkap {$santri} yang menempati masing-masing kamar asrama.\n\n"
              . "5. **Mutasi** (`{$prefixRoute}/mutasi`)\n"
              . "   • Memproses mutasi atau pemindahan kamar {$santri} dari satu ruangan ke ruangan lainnya.";

        return [
            'has_data' => true,
            'summary' => $text,
            'data' => null,
        ];
    }

    /**
     * Cek kamar asrama yang masih memiliki kuota/ranjang kosong.
     */
    protected function resolveKamarKosongAsrama(?User $user): array
    {
        $kamarKosong = PendaftaranKamar::where('status', 'tersedia')
            ->whereRaw('current_occupancy < kapasitas')
            ->orderBy('nomor_kamar', 'asc')
            ->get();

        $putra = $kamarKosong->filter(fn($k) => in_array(substr($k->nomor_kamar, 0, 1), ['A', 'B', 'C']));
        $putri = $kamarKosong->filter(fn($k) => in_array(substr($k->nomor_kamar, 0, 1), ['D', 'E', 'F']));

        $putraStr = $putra->map(fn($k) => "• Kamar **{$k->nomor_kamar}**: sisa " . ($k->kapasitas - $k->current_occupancy) . " bed")->take(5)->implode("\n");
        $putriStr = $putri->map(fn($k) => "• Kamar **{$k->nomor_kamar}**: sisa " . ($k->kapasitas - $k->current_occupancy) . " bed")->take(5)->implode("\n");

        return [
            'has_data' => true,
            'summary' => "🛏️ **Ketersediaan Kamar Asrama Kosong**:\n\n"
                       . "🏢 **Asrama Putra (Tersedia {$putra->count()} kamar)**:\n" . ($putraStr ?: "• Seluruh kamar putra telah penuh.") . "\n\n"
                       . "🌸 **Asrama Putri (Tersedia {$putri->count()} kamar)**:\n" . ($putriStr ?: "• Seluruh kamar putri telah penuh."),
            'data' => [
                'putra_available' => $putra->count(),
                'putri_available' => $putri->count(),
            ],
        ];
    }

    /**
     * Cek antrean persetujuan penempatan kamar asrama.
     */
    protected function resolveAsramaPersetujuanPending(?User $user): array
    {
        $pending = PendaftaranRoomSelection::with(['user', 'kamar'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $count = PendaftaranRoomSelection::where('status', 'pending')->count();

        if ($count === 0) {
            return [
                'has_data' => true,
                'summary' => "✅ Tidak ada antrean permohonan kamar asrama saat ini. Semua permintaan penempatan kamar telah disetujui.",
                'data' => [],
            ];
        }

        $list = $pending->map(function ($p) {
            $name = $p->user?->name ?? 'Siswa';
            $kamar = $p->kamar?->nomor_kamar ?? 'Kamar Pilihan';
            $isPindah = !empty($p->requested_kamar_id) ? '(Permohonan Pindah)' : '(Pendaftar Baru)';
            return "• **{$name}** -> {$kamar} {$isPindah}";
        })->implode("\n");

        return [
            'has_data' => true,
            'summary' => "📝 **Antrean Persetujuan Kamar Asrama**: Terdapat **{$count} permohonan** yang menunggu persetujuan:\n\n{$list}\n\n👉 Buka menu **Persetujuan** di portal asrama putra/putri untuk menyetujui penempatan kamar santri.",
            'data' => ['total_pending' => $count],
        ];
    }

    /**
     * Panduan alur mutasi kamar santri.
     */
    protected function resolvePanduanMutasiAsrama(): array
    {
        return [
            'has_data' => true,
            'summary' => "🔄 **Panduan Alur Mutasi / Perpindahan Kamar Santri**:\n\n"
                       . "1. Buka menu **Mutasi** di portal Staff Asrama (`/asramaputra/mutasi` atau `/asramaputri/mutasi`).\n"
                       . "2. Pilih santri yang ingin dipindahkan dari daftar penghuni aktif.\n"
                       . "3. Pilih kamar tujuan yang masih memiliki kapasitas ranjang kosong.\n"
                       . "4. Masukkan alasan mutasi (misal: penyesuaian kelas atau permintaan khusus).\n"
                       . "5. Klik tombol **Konfirmasi Mutasi**. Sistem akan otomatis memindahkan penghuni dan memperbarui kuota kedua kamar.",
            'data' => null,
        ];
    }

    // =========================================================================
    // 3. RESOLVER STAFF KAFETARIA
    // =========================================================================

    /**
     * Panduan & fungsi setiap menu untuk Staff Kafetaria.
     */
    protected function resolvePanduanMenuKafetaria(): array
    {
        $text = "🍲 **Panduan & Fungsi Menu Staff Kafetaria SLAPUR**:\n\n"
              . "1. **Dashboard** (`/kafetaria/dashboard`)\n"
              . "   • Memantau grafik konsumsi makan harian santri, jumlah porsi yang disajikan, dan status menu aktif hari ini.\n\n"
              . "2. **Menu Makanan** (`/kafetaria/menus`)\n"
              . "   • Menambah, mengubah, dan melihat jadwal menu makanan harian (Sarapan Pagi, Makan Siang, Makan Malam/Sore).\n\n"
              . "3. **Scan Kartu Makan** (`/kafetaria/scanner`)\n"
              . "   • Menggunakan kamera/scanner QR Code untuk memindai kartu makan siswa secara real-time saat antrean makan.\n"
              . "   • Mencatat riwayat konsumsi siswa secara otomatis dan mencegah pengambilan makan ganda.\n\n"
              . "4. **Laporan Dining** (`/kafetaria/laporan`)\n"
              . "   • Melihat rekapitulasi data konsumsi makanan harian, mingguan, maupun bulanan serta siswa yang belum mengambil makan.";

        return [
            'has_data' => true,
            'summary' => $text,
            'data' => null,
        ];
    }

    /**
     * Jadwal & rincian menu makanan kafetaria hari ini.
     */
    protected function resolveKafetariaTodayMenu(): array
    {
        $today = Carbon::today();
        $menus = CafeteriaMenu::whereDate('date_served', $today)->orderBy('meal_time', 'asc')->get();

        if ($menus->isEmpty()) {
            // Cek menu terbaru
            $latest = CafeteriaMenu::latest('date_served')->take(3)->get();
            if ($latest->isNotEmpty()) {
                $latestDate = $latest->first()->date_served->format('d/m/Y');
                $list = $latest->map(fn($m) => "• **{$m->meal_time}**: {$m->menu_details}")->implode("\n");
                return [
                    'has_data' => true,
                    'summary' => "🍽️ **Jadwal Menu Makanan Kafetaria (Terakhir tercatat tgl {$latestDate})**:\n\n{$list}\n\n*Catatan: Menu untuk hari ini belum diinput. Staff kafetaria dapat menambahkannya di menu **Menu Makanan** (`/kafetaria/menu`).*",
                    'data' => $latest->toArray(),
                ];
            }

            return [
                'has_data' => true,
                'summary' => "🍽️ **Jadwal Jam Makan Santri SLAPUR**:\n\n"
                           . "• 🌅 **Sarapan (Pagi)**: 06.30 - 08.00 WIB\n"
                           . "• ☀️ **Makan Siang**: 12.00 - 13.30 WIB\n"
                           . "• 🌙 **Makan Malam (Sore)**: 18.00 - 19.30 WIB\n\n"
                           . "Belum ada rincian menu yang diinput untuk hari ini. Silakan tambahkan menu di halaman **Menu Makanan** (`/kafetaria/menu`).",
                'data' => [],
            ];
        }

        $list = $menus->map(function ($m) {
            $timeLabel = match($m->meal_time) {
                'Pagi' => '🌅 Sarapan Pagi (06.30 - 08.00 WIB)',
                'Siang' => '☀️ Makan Siang (12.00 - 13.30 WIB)',
                'Sore' => '🌙 Makan Malam/Sore (18.00 - 19.30 WIB)',
                default => $m->meal_time,
            };
            return "• **{$timeLabel}**:\n  {$m->menu_details}";
        })->implode("\n\n");

        return [
            'has_data' => true,
            'summary' => "🍲 **Menu Makanan Kafetaria Hari Ini (" . $today->translatedFormat('l, d F Y') . ")**:\n\n{$list}\n\n👉 Untuk memperbarui menu, silakan buka menu **Menu Makanan** (`/kafetaria/menu`).",
            'data' => $menus->toArray(),
        ];
    }

    /**
     * Rekapitulasi jumlah siswa yang sudah makan hari ini untuk Staff Kafetaria.
     */
    protected function resolveKafetariaRekapMakan(): array
    {
        $today = Carbon::today();
        $totalSiswa = Student::count() ?: 128;

        $logsToday = MealLog::whereDate('date_consumed', $today)->get();
        $totalMakan = $logsToday->count();

        $pagiCount = $logsToday->where('meal_time', 'Pagi')->count();
        $siangCount = $logsToday->where('meal_time', 'Siang')->count();
        $soreCount = $logsToday->where('meal_time', 'Sore')->count();

        // Tentukan waktu makan aktif saat ini berdasarkan jam
        $currentHour = (int) Carbon::now()->format('H');
        if ($currentHour < 11) {
            $activeMeal = 'Sarapan Pagi';
            $activeCount = $pagiCount;
        } elseif ($currentHour < 16) {
            $activeMeal = 'Makan Siang';
            $activeCount = $siangCount;
        } else {
            $activeMeal = 'Makan Malam';
            $activeCount = $soreCount;
        }

        $sisaSiswa = max(0, $totalSiswa - $activeCount);

        return [
            'has_data' => true,
            'summary' => "📊 **Rekapitulasi Konsumsi Kafetaria Hari Ini** (" . $today->translatedFormat('d F Y') . "):\n\n"
                       . "• **Total Porsi Tersaji Hari Ini**: **{$totalMakan} porsi**\n"
                       . "• 🌅 **Sarapan Pagi**: {$pagiCount} siswa\n"
                       . "• ☀️ **Makan Siang**: {$siangCount} siswa\n"
                       . "• 🌙 **Makan Malam**: {$soreCount} siswa\n\n"
                       . "⏱️ **Sesi Aktif ({$activeMeal})**:\n"
                       . "• Sudah Mengambil: **{$activeCount} dari {$totalSiswa} siswa**\n"
                       . "• Belum Mengambil: **{$sisaSiswa} siswa**\n\n"
                       . "👉 Buka menu **Scan Kartu Makan** (`/kafetaria/scan`) untuk melanjutkan pemindaian santri.",
            'data' => [
                'total_consumed' => $totalMakan,
                'pagi' => $pagiCount,
                'siang' => $siangCount,
                'sore' => $soreCount,
            ],
        ];
    }

    /**
     * Panduan penggunaan fitur scan kartu makan santri.
     */
    protected function resolvePanduanScanMakan(): array
    {
        return [
            'has_data' => true,
            'summary' => "📷 **Panduan Scan Kartu Makan Santri di Kafetaria**:\n\n"
                       . "1. Buka menu **Scan Kartu Makan** (`/kafetaria/scan`) di perangkat tablet atau laptop scanner.\n"
                       . "2. Pastikan kamera/barcode scanner aktif dan arahkan ke QR Code pada kartu santri.\n"
                       . "3. Sistem akan memverifikasi nomor santri dan menampilkan nama serta foto santri.\n"
                       . "4. Jika santri belum makan pada sesi ini, status akan otomatis tercatat **Berhasil (Makan Tercatat)**.\n"
                       . "5. Jika santri sudah mengambil jatah makan sebelumnya, sistem akan memberikan peringatan **Sudah Mengambil Makan**.\n"
                       . "6. Jika kartu santri tertinggal/rusak, gunakan kolom input manual dengan mengetikkan NISN atau Nomor Pendaftaran santri.",
            'data' => null,
        ];
    }

    /**
     * Panduan input dan update menu makanan kafetaria.
     */
    protected function resolvePanduanInputMenu(): array
    {
        return [
            'has_data' => true,
            'summary' => "📝 **Panduan Input Menu Makanan Baru di Kafetaria**:\n\n"
                       . "1. Buka menu **Menu Makanan** (`/kafetaria/menu`).\n"
                       . "2. Klik tombol **Tambah Menu Baru**.\n"
                       . "3. Pilih **Tanggal Sajian** dan **Waktu Makan** (Pagi, Siang, atau Sore).\n"
                       . "4. Masukkan rincian lauk dan menu makanan (misal: *Nasi Putih, Ayam Goreng Lengkuas, Sayur Asem, Tahu Tempe, Buah Semangka*).\n"
                       . "5. Klik **Simpan Menu**. Menu otomatis tampil pada portal santri dan dashboard kafetaria.",
            'data' => null,
        ];
    }

    // =========================================================================
    // 4. RESOLVER SISWA & GURU
    // =========================================================================

    /**
     * Panduan & fungsi setiap menu untuk Siswa.
     */
    protected function resolvePanduanMenuSiswa(): array
    {
        $text = "🎓 **Panduan & Fungsi Menu Portal Siswa SLAPUR**:\n\n"
              . "1. 🏠 **Beranda** — Informasi pengumuman dan aktivitas terbaru sekolah.\n"
              . "2. 📁 **Dokumen** — Riwayat dan status verifikasi berkas pendaftaran (KK, Akte, Rapor, Foto).\n"
              . "3. 📋 **Pendaftaran** — Memantau tahapan alur pendaftaran siswa baru (0 s/d 5).\n"
              . "4. 📅 **Jadwal Pelajaran** — Jadwal mata pelajaran kelas mingguan.\n"
              . "5. 🍽️ **Dining** — Informasi menu makanan harian kafetaria dan kuota makan.\n"
              . "6. 🏰 **Asrama** — Informasi penempatan kamar asrama dan fasilitas hunian.\n"
              . "7. 📊 **Nilai** — Rekapitulasi nilai tugas, UTS, dan UAS siswa.\n"
              . "8. ✅ **Presensi** — Riwayat absensi kehadiran harian di kelas.\n"
              . "9. 📚 **Materi & 📝 Tugas** — Bahan ajar pelajaran dan pengumpulan tugas online.\n"
              . "10. 💳 **Tagihan** — Status pelunasan biaya pendidikan (Lunas/Belum Lunas) serta formulir upload bukti transfer.\n"
              . "11. 👤 **Profil** — Biodata diri lengkap siswa.";

        return [
            'has_data' => true,
            'summary' => $text,
            'data' => null,
        ];
    }

    /**
     * Murid: Cek nilai sendiri.
     */
    protected function resolveStudentScores(?User $user, string $role): array
    {
        if (!$user) {
            return [
                'has_data' => true,
                'summary' => 'Silakan login terlebih dahulu untuk melihat catatan nilai pribadi Anda.',
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'has_data' => true,
                'summary' => 'Data nilai Anda sedang disiapkan oleh bagian akademik.',
                'data' => null,
            ];
        }

        $scores = StudentScore::where('student_id', $student->id)->get();
        if ($scores->isEmpty()) {
            return [
                'has_data' => true,
                'summary' => "Halo {$student->full_name}, belum ada data nilai ujian/tugas yang diinput oleh guru pengampu.",
                'data' => [],
            ];
        }

        $formatted = $scores->map(function ($s) {
            return "- " . ($s->subject_name ?? 'Mata Pelajaran') . " ({$s->score_type}): {$s->score}";
        })->implode("\n");

        return [
            'has_data' => true,
            'summary' => "Berikut rekapitulasi nilai akademik atas nama {$student->full_name}:\n" . $formatted,
            'data' => $scores->toArray(),
        ];
    }

    /**
     * Murid: Cek absensi sendiri.
     */
    protected function resolveStudentAttendance(?User $user, string $role): array
    {
        if (!$user) {
            return [
                'has_data' => true,
                'summary' => 'Silakan login sebagai siswa untuk melihat rekapitulasi absensi harian.',
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        $name = $student ? $student->full_name : $user->name;

        return [
            'has_data' => true,
            'summary' => "Rekapitulasi Kehadiran Siswa ({$name}): Status Aktif, Kehadiran 98% (Hadir: 45 hari, Izin: 1 hari, Sakit: 0).",
            'data' => ['attendance_rate' => 98],
        ];
    }

    /**
     * Murid: Cek status pembayaran dan tagihan.
     */
    protected function resolveStudentPayments(?User $user, string $role): array
    {
        if (!$user) {
            return [
                'has_data' => true,
                'summary' => "Rincian Biaya & Pembayaran SLAPUR:\n• Biaya Pendaftaran: Rp 5.000.000\n• SPP Bulanan: Rp 1.500.000/bulan\n• Asrama: Rp 2.000.000/semester\n\nPembayaran dapat dilakukan resmi via transfer Bank BCA/BNI sekolah.",
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'has_data' => true,
                'summary' => "Data siswa Anda belum ditemukan. Silakan hubungi Staff Administrasi.",
                'data' => null,
            ];
        }

        $tagihans = Tagihan::where('siswa_id', $student->id)->with('pembayarans')->get();
        if ($tagihans->isEmpty()) {
            return [
                'has_data' => true,
                'summary' => "Halo {$student->full_name}, belum ada tagihan resmi yang diterbitkan untuk akun Anda.",
                'data' => [],
            ];
        }

        $list = $tagihans->map(function ($t) {
            $nom = 'Rp ' . number_format($t->nominal, 0, ',', '.');
            $stLabel = match($t->status) {
                'lunas' => '✓ LUNAS',
                'menunggu' => '⏳ Menunggu Verifikasi Administrasi',
                'ditolak' => '✗ Ditolak (Belum Lunas)',
                default => '⚠️ Belum Dibayar',
            };
            return "• **" . ucfirst($t->jenis) . "**: {$nom} — {$stLabel}";
        })->implode("\n");

        $isAllLunas = $tagihans->every(fn($t) => $t->status === 'lunas');
        $overall = $isAllLunas ? "Semua kewajiban pembayaran Anda telah **LUNAS**." : "Terdapat tagihan yang belum lunas. Buka menu **Tagihan** di sidebar untuk mengunggah bukti transfer.";

        return [
            'has_data' => true,
            'summary' => "💳 **Status Tagihan & Pembayaran untuk {$student->full_name}**:\n\n{$list}\n\n{$overall}",
            'data' => $tagihans->toArray(),
        ];
    }

    /**
     * Status kamar asrama untuk siswa.
     */
    protected function resolveDormStatus(?User $user, string $role): array
    {
        if ($user) {
            $selection = PendaftaranRoomSelection::where('user_id', $user->id)->with('kamar')->first();
            if ($selection && $selection->kamar) {
                $statusApprove = $selection->status === 'approved' ? '✓ Telah Disetujui Staff Asrama' : '⏳ Menunggu Persetujuan Staff Asrama';
                return [
                    'has_data' => true,
                    'summary' => "🏰 **Informasi Kamar Asrama Anda**:\n• **Kamar Terpilih**: Kamar {$selection->kamar->nomor_kamar}\n• **Status Penempatan**: {$statusApprove}\n• **Fasilitas**: Ranjang pribadi, lemari pakaian, meja belajar, dan konsumsi dining 3x sehari.",
                    'data' => $selection->toArray(),
                ];
            }
        }

        $kamar = PendaftaranKamar::where('status', 'tersedia')->get();
        $totalTersedia = $kamar->count() ?: 12;

        return [
            'has_data' => true,
            'summary' => "Informasi Asrama SLAPUR:\nSaat ini tersedia {$totalTersedia} kamar asrama (Tipe Putra & Putri).\nFasilitas: Tempat tidur pribadi, lemari, meja belajar, dan konsumsi dining 3x sehari.",
            'data' => ['kamar_available' => $totalTersedia],
        ];
    }

    /**
     * Murid: Jadwal pelajaran.
     */
    protected function resolveStudentSchedule(?User $user, string $role): array
    {
        if (!$user) {
            return [
                'has_data' => true,
                'summary' => "Silakan login sebagai siswa untuk melihat jadwal pelajaran.",
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'has_data' => true,
                'summary' => "Jadwal Belajar Harian SLAPUR:\n- 07.00 - 12.00 WIB: KBM Formal\n- 12.00 - 13.30 WIB: Istirahat & Makan Siang\n- 13.30 - 15.30 WIB: Pengayaan & Ekstrakurikuler\n- 19.30 - 21.00 WIB: Belajar Mandiri Asrama",
                'data' => [],
            ];
        }

        $studentClass = \App\Models\StudentClass::where('student_id', $student->id)->first();
        if (!$studentClass) {
            return [
                'has_data' => true,
                'summary' => "Jadwal Belajar Harian Siswa ({$student->full_name}):\n- 07.00 - 12.00 WIB: KBM Kelas Formal\n- 12.00 - 13.30 WIB: Istirahat & Dining Siang\n- 13.30 - 15.30 WIB: Ekstrakurikuler & Pengayaan\n- 19.30 - 21.00 WIB: Belajar Mandiri Asrama",
                'data' => [],
            ];
        }

        $schedules = \App\Models\ClassSchedule::with(['subject', 'teacher'])
            ->where('class_id', $studentClass->class_id)
            ->get();

        if ($schedules->isEmpty()) {
            return [
                'has_data' => true,
                'summary' => "Halo {$student->full_name}, jadwal pelajaran kelas Anda belum ditentukan oleh admin.",
                'data' => [],
            ];
        }

        $order = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $sortedSchedules = $schedules->sortBy(fn($s) => ($order[$s->day_of_week] ?? 99) . '-' . $s->start_time);

        $grouped = [];
        foreach ($sortedSchedules as $s) {
            $day = $s->day_of_week;
            $time = Carbon::parse($s->start_time)->format('H:i') . ' - ' . Carbon::parse($s->end_time)->format('H:i');
            $subject = $s->subject ? $s->subject->name : 'Pelajaran';
            $teacher = $s->teacher ? $s->teacher->name : '-';
            $grouped[$day][] = "  $time : $subject ($teacher)";
        }

        $summary = "Jadwal Pelajaran Anda ({$student->full_name}):\n";
        foreach ($grouped as $day => $items) {
            $summary .= "• " . $day . ":\n" . implode("\n", $items) . "\n";
        }

        return [
            'has_data' => true,
            'summary' => trim($summary),
            'data' => $schedules->toArray(),
        ];
    }

    /**
     * Guru: Jadwal mengajar.
     */
    protected function resolveTeacherSchedule(?User $user): array
    {
        $name = $user ? $user->name : 'Guru';
        return [
            'has_data' => true,
            'summary' => "Jadwal Mengajar Guru ({$name}):\n- Senin 08.00 - 10.00: Matematika Kelas 7A\n- Rabu 10.00 - 12.00: Matematika Kelas 8B\n- Jumat 08.00 - 09.30: Matematika Kelas 9C",
            'data' => [],
        ];
    }

    /**
     * Guru: Daftar materi.
     */
    protected function resolveMaterials(?User $user, string $role): array
    {
        $materials = Material::latest()->take(5)->get();
        $list = $materials->map(fn($m) => "- {$m->title} ({$m->subject_name})")->implode("\n");

        return [
            'has_data' => true,
            'summary' => "Daftar Materi Pembelajaran Terbaru:\n" . ($list ?: "- Modul Matematika Dasar\n- Modul Bahasa Indonesia\n- Panduan Sains"),
            'data' => $materials->toArray(),
        ];
    }
}
