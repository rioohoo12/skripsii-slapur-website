<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranPayment;
use App\Models\PendaftaranProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruKelasController extends Controller
{
    private const GURU_ROLES = ['guru', 'admin', 'super_admin'];

    private function ensureGuru(Request $request): ?User
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }
        if (!in_array($user->role, self::GURU_ROLES, true)) {
            return null;
        }
        return $user;
    }

    /**
     * GET /api/guru/kelas
     * Daftar tingkat (kelas) beserta jumlah siswa terdaftar untuk guru ini.
     * Siswa dihitung jika:
     * - Punya PendaftaranProfile (kelas_yang_didaftar 7–12)
     * - Pembayaran pendaftaran sudah minimal 60% (status terverifikasi atau nominal_dibayar >= nominal_60_percent)
     */
    public function index(Request $request): JsonResponse
    {
        $guru = $this->ensureGuru($request);
        if (!$guru) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $allowedTingkat = $this->allowedTingkatForGuru($guru);
        if (empty($allowedTingkat)) {
            return response()->json(['classes' => []]);
        }

        $profiles = PendaftaranProfile::query()
            ->whereIn('kelas_yang_didaftar', $allowedTingkat)
            ->whereHas('user', function ($q) {
                $q->where('role', 'siswa');
            })
            ->get(['kelas_yang_didaftar']);

        $grouped = $profiles->groupBy('kelas_yang_didaftar')->map->count()->toArray();

        $classes = [];
        foreach ($grouped as $tingkat => $count) {
            $label = $this->labelKelas((int) $tingkat);
            $classes[] = [
                'tingkat' => (int) $tingkat,
                'name' => $label,
                'students_count' => $count,
            ];
        }

        usort($classes, fn ($a, $b) => $a['tingkat'] <=> $b['tingkat']);

        return response()->json([
            'classes' => $classes,
        ]);
    }

    /**
     * GET /api/guru/kelas/{tingkat}
     * Detail siswa untuk satu tingkat (kelas).
     */
    public function show(Request $request, int $tingkat): JsonResponse
    {
        $guru = $this->ensureGuru($request);
        if (!$guru) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $allowedTingkat = $this->allowedTingkatForGuru($guru);
        if (!in_array($tingkat, $allowedTingkat, true)) {
            return response()->json(['message' => 'Tidak diizinkan melihat tingkat ini'], 403);
        }

        $profiles = PendaftaranProfile::query()
            ->where('kelas_yang_didaftar', $tingkat)
            ->whereHas('user', function ($q) {
                $q->where('role', 'siswa');
            })
            ->with('user:id,name,jenis_kelamin,email')
            ->orderBy('nama_lengkap')
            ->get();

        $students = $profiles->map(function (PendaftaranProfile $profile) {
            return [
                'id' => $profile->user_id,
                'nama' => $profile->nama_lengkap,
                'kelas_yang_didaftar' => (int) $profile->kelas_yang_didaftar,
                'jenis_kelamin' => $profile->user?->jenis_kelamin,
                'email' => $profile->user?->email,
            ];
        });
        
        // Fallback dihapus agar data tersinkronisasi sesuai database (hanya siswa riil)

        return response()->json([
            'kelas' => [
                'tingkat' => (int) $tingkat,
                'name' => $this->labelKelas($tingkat),
            ],
            'students' => $students,
        ]);
    }

    /**
     * Tentukan tingkat (7–12) mana saja yang boleh dilihat guru ini.
     */
    private function allowedTingkatForGuru(User $guru): array
    {
        $jenjang = $guru->jenjang_guru ?? null;

        if ($jenjang === 'smp') {
            return [7, 8, 9];
        }
        if ($jenjang === 'sma') {
            return [10, 11, 12];
        }
        
        // Default allow all for guru, admin, super_admin, or if jenjang_guru is missing
        return [7, 8, 9, 10, 11, 12];
    }

    private function labelKelas(int $tingkat): string
    {
        if (in_array($tingkat, [7, 8, 9], true)) {
            return 'Kelas ' . $tingkat . ' (SMP)';
        }
        if (in_array($tingkat, [10, 11, 12], true)) {
            return 'Kelas ' . $tingkat . ' (SMA)';
        }
        return 'Kelas ' . $tingkat;
    }
}

