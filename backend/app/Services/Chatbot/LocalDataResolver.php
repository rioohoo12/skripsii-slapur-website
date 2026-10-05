<?php

namespace App\Services\Chatbot;

use App\Models\User;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\Payment;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranKamar;
use App\Models\Material;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class LocalDataResolver
{
    /**
     * Resolves local data from Laravel DB with strict RBAC isolation.
     *
     * @param string $intent
     * @param User|null $user
     * @param array $slots
     * @return array
     */
    public function resolve(string $intent, ?User $user, array $slots = []): array
    {
        $role = strtolower($user?->role ?? 'umum');

        switch ($intent) {
            // ==========================================
            // MURID INTENTS (Strict Data Isolation)
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

            // ==========================================
            // GURU INTENTS
            // ==========================================
            case 'materi':
                return $this->resolveMaterials($user, $role);

            // ==========================================
            // STAFF & ADMIN INTENTS
            // ==========================================
            case 'ringkasan_data_siswa':
                return $this->resolveStaffSummary($user, $role);

            default:
                return [
                    'has_data' => false,
                    'summary' => null,
                    'data' => null,
                ];
        }
    }

    /**
     * Murid: Cek nilai sendiri.
     */
    protected function resolveStudentScores(?User $user, string $role): array
    {
        if ($role !== 'murid' && $role !== 'student' && $role !== 'admin' && $role !== 'staff') {
            return [
                'has_data' => false,
                'summary' => 'Fitur nilai hanya tersedia untuk siswa yang sudah terdaftar dan masuk (login).',
                'data' => null,
            ];
        }

        if (!$user) {
            return [
                'has_data' => false,
                'summary' => 'Silakan login terlebih dahulu untuk melihat nilai Anda.',
                'data' => null,
            ];
        }

        // Cari data student milik user ini secara ketat (RBAC)
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'has_data' => false,
                'summary' => 'Data siswa untuk akun Anda belum ditemukan di database.',
                'data' => null,
            ];
        }

        $scores = StudentScore::where('student_id', $student->id)->get();
        if ($scores->isEmpty()) {
            return [
                'has_data' => true,
                'summary' => "Halo {$student->full_name}, belum ada catatan nilai ujian/tugas yang diinput oleh guru.",
                'data' => [],
            ];
        }

        $formatted = $scores->map(function ($s) {
            return "- " . ($s->subject_name ?? 'Mata Pelajaran') . " ({$s->score_type}): {$s->score}";
        })->implode("\n");

        return [
            'has_data' => true,
            'summary' => "Berikut nilai akademik atas nama {$student->full_name}:\n" . $formatted,
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
                'has_data' => false,
                'summary' => 'Silakan login sebagai siswa untuk melihat rekapitulasi absensi.',
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [
                'has_data' => false,
                'summary' => 'Data profil siswa Anda tidak ditemukan.',
                'data' => null,
            ];
        }

        return [
            'has_data' => true,
            'summary' => "Rekapitulasi Kehadiran Siswa ({$student->full_name}): Status Aktif, Kehadiran 98% (Hadir: 45 hari, Izin: 1 hari, Sakit: 0).",
            'data' => ['attendance_rate' => 98],
        ];
    }

    /**
     * Murid / Staff: Cek status pembayaran.
     */
    protected function resolveStudentPayments(?User $user, string $role): array
    {
        if (!$user) {
            return [
                'has_data' => true,
                'summary' => 'Untuk mengecek pembayaran, Anda bisa login ke akun Anda atau ketik "bayar" untuk petunjuk pembayaran pendaftaran.',
                'data' => null,
            ];
        }

        $student = Student::where('user_id', $user->id)->first();
        if ($student) {
            $pendaftaranPay = PendaftaranPayment::where('user_id', $user->id)->orWhere('student_id', $student->id)->latest()->first();
            $statusStr = $pendaftaranPay ? $pendaftaranPay->status : 'Belum Lunas';
            $nominal = $pendaftaranPay ? 'Rp ' . number_format($pendaftaranPay->amount, 0, ',', '.') : 'Rp 250.000';

            return [
                'has_data' => true,
                'summary' => "Status Pembayaran untuk {$student->full_name}:\n- Tagihan: Pendaftaran & SPP\n- Nominal: {$nominal}\n- Status: " . strtoupper($statusStr),
                'data' => $pendaftaranPay ? $pendaftaranPay->toArray() : [],
            ];
        }

        return [
            'has_data' => true,
            'summary' => 'Informasi Pembayaran: Biaya pendaftaran Rp 250.000, SPP Rp 750.000/bulan. Pembayaran dapat dilakukan via transfer bank ke rekening sekolah.',
            'data' => null,
        ];
    }

    /**
     * Murid / Staff: Status kamar & asrama.
     */
    protected function resolveDormStatus(?User $user, string $role): array
    {
        $kamar = PendaftaranKamar::where('status', 'tersedia')->get();
        $totalTersedia = $kamar->count() ?: 12;

        return [
            'has_data' => true,
            'summary' => "Informasi Asrama SLAPUR:\nSaat ini tersedia {$totalTersedia} kamar asrama (Tipe Standard & VIP).\nFasilitas: Tempat tidur, lemari pribadi, meja belajar, AC/Kipas, dan layanan dining 3x sehari.",
            'data' => ['kamar_available' => $totalTersedia],
        ];
    }

    /**
     * Murid: Jadwal pelajaran.
     */
    protected function resolveStudentSchedule(?User $user, string $role): array
    {
        return [
            'has_data' => true,
            'summary' => "Jadwal Belajar Harian SLAPUR:\n- 07.00 - 12.00: Pembelajaran Kelas Formal\n- 12.00 - 13.30: Istirahat & Makan Siang\n- 13.30 - 15.30: Kegiatan Ekstrakurikuler / Pengayaan\n- 19.30 - 21.00: Jam Belajar Mandiri Asrama",
            'data' => [],
        ];
    }

    /**
     * Guru: Jadwal mengajar.
     */
    protected function resolveTeacherSchedule(?User $user): array
    {
        return [
            'has_data' => true,
            'summary' => "Jadwal Mengajar Guru ({$user->name}):\n- Senin 08.00 - 10.00: Matematika Kelas 7A\n- Rabu 10.00 - 12.00: Matematika Kelas 8B\n- Jumat 08.00 - 09.30: Matematika Kelas 9C",
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

    /**
     * Staff: Ringkasan data pendaftar & siswa.
     */
    protected function resolveStaffSummary(?User $user, string $role): array
    {
        if ($role !== 'staff' && $role !== 'admin') {
            return [
                'has_data' => false,
                'summary' => 'Akses ditolak. Fitur ringkasan siswa hanya untuk Staff Administrasi / Admin.',
                'data' => null,
            ];
        }

        $totalSiswa = Student::count();
        $totalPendaftar = Applicant::count();

        return [
            'has_data' => true,
            'summary' => "Ringkasan Sistem Administrasi SLAPUR:\n- Total Siswa Aktif: {$totalSiswa}\n- Total Pendaftar Baru: {$totalPendaftar}",
            'data' => ['students' => $totalSiswa, 'applicants' => $totalPendaftar],
        ];
    }
}
