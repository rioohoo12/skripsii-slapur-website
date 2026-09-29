<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealLog;
use App\Models\PendaftaranProfile;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DiningController extends Controller
{
    private function ensureJsonInput(Request $request): void
    {
        $contentType = $request->header('Content-Type', '');
        if (str_starts_with($contentType, 'application/json') && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            if (is_array($data)) {
                $request->merge($data);
            }
        }
    }

    /**
     * GET /api/dining/info
     * Mendapatkan nomor makan siswa & Laporan Makan di Dining (riwayat presensi makan).
     */
    public function getInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $student = Student::where('user_id', $user->id)->first();

        // Jika murid belum punya record Student, buatkan record baru otomatis
        if (!$student) {
            $count = Student::count() + 1;
            $paddedNum = str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $student = Student::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'gender' => $user->jenis_kelamin === 'perempuan' ? 'P' : 'L',
                'nomor_pendaftaran' => 'REG-' . date('Ym') . '-' . $paddedNum,
                'dining_number' => $paddedNum,
                'status_pendaftaran' => 'terdaftar',
            ]);
        }

        $profile = PendaftaranProfile::where('user_id', $user->id)->first();
        $diningNumber = $student->getDiningNumberFormatted();
        $registrationNumber = $student->nomor_pendaftaran ?? 'REG-202609-0001';

        // Ambil Laporan Makan di Dining untuk siswa ini
        $rawLogs = MealLog::where('student_id', $student->id)
            ->orderBy('scanned_at', 'desc')
            ->get();

        $logs = [];
        $no = 1;
        foreach ($rawLogs as $log) {
            $scanned = Carbon::parse($log->scanned_at);
            $dateCons = Carbon::parse($log->date_consumed);
            $logs[] = [
                'no' => $no++,
                'id' => $log->id,
                'tanggal' => $dateCons->format('d M Y'),
                'raw_date' => $dateCons->format('Y-m-d'),
                'waktu' => $scanned->format('H:i') . ' WIB',
                'jenis_makan' => ucfirst(strtolower($log->meal_time)), // Pagi, Siang, Sore
            ];
        }

        // Hitung statistik makan
        $totalMakan = count($logs);
        $makanPagi = count(array_filter($logs, fn($l) => strtolower($l['jenis_makan']) === 'pagi'));
        $makanSiang = count(array_filter($logs, fn($l) => strtolower($l['jenis_makan']) === 'siang'));
        $makanSore = count(array_filter($logs, fn($l) => in_array(strtolower($l['jenis_makan']), ['sore', 'malam'])));

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'dining_number' => $diningNumber,
            'dining_status' => $student->dining_status ?? 'active',
            'registration_number' => $registrationNumber,
            'class' => $profile?->kelas_yang_didaftar ?? '7',
            'table_number' => 'Meja #' . $diningNumber,
            'summary' => [
                'total_makan' => $totalMakan,
                'pagi' => $makanPagi,
                'siang' => $makanSiang,
                'sore' => $makanSore,
            ],
            'laporan_makan' => $logs,
        ]);
    }

    /**
     * POST /api/staff/dining/input
     * Diinput oleh staff dining dengan memasukkan nomor makan siswa (contoh: 001).
     */
    public function recordMeal(Request $request): JsonResponse
    {
        $this->ensureJsonInput($request);

        $validated = $request->validate([
            'dining_number' => 'required|string',
            'meal_time' => 'required|in:pagi,siang,sore,malam,Pagi,Siang,Sore,Malam',
        ]);

        $rawNumber = trim($validated['dining_number']);
        $mealTime = strtolower(trim($validated['meal_time']));
        if ($mealTime === 'malam') {
            $mealTime = 'sore';
        }

        // Format nomor urut pencarian (misal "1" -> "001")
        $paddedNumber = str_pad(preg_replace('/\D/', '', $rawNumber) ?: $rawNumber, 3, '0', STR_PAD_LEFT);

        // Cari student berdasarkan dining_number, id, user_id, atau nomor_pendaftaran
        $student = Student::where('dining_number', $paddedNumber)
            ->orWhere('dining_number', $rawNumber)
            ->orWhere('id', (int) $rawNumber)
            ->orWhere('user_id', (int) $rawNumber)
            ->orWhere('nomor_pendaftaran', 'LIKE', '%' . $paddedNumber)
            ->first();

        if (!$student) {
            // Jika belum ada student record tetapi ada user, buatkan otomatis
            $profile = PendaftaranProfile::where('user_id', (int) $rawNumber)->first();
            if ($profile) {
                $student = Student::create([
                    'user_id' => $profile->user_id,
                    'full_name' => $profile->nama_lengkap,
                    'gender' => 'L',
                    'dining_number' => $paddedNumber,
                    'nomor_pendaftaran' => 'REG-' . date('Ym') . '-' . $paddedNumber,
                ]);
            } else {
                return response()->json([
                    'message' => "Siswa dengan nomor makan '{$rawNumber}' tidak ditemukan.",
                ], 404);
            }
        }

        $today = now()->toDateString();

        // Cari apakah log presensi makan untuk jenis makan & tanggal ini sudah ada
        $existingLog = MealLog::where('student_id', $student->id)
            ->whereDate('date_consumed', $today)
            ->where('meal_time', $mealTime)
            ->first();

        if ($existingLog) {
            $existingLog->update(['scanned_at' => now()]);
            $mealLog = $existingLog;
        } else {
            $mealLog = MealLog::create([
                'student_id' => $student->id,
                'date_consumed' => $today,
                'meal_time' => $mealTime,
                'eating_number' => (int) ($student->dining_number ?: $student->id),
                'scanned_at' => now(),
            ]);
        }

        return response()->json([
            'message' => "Presensi makan ({$mealTime}) berhasil dicatat untuk Siswa #{$student->getDiningNumberFormatted()} ({$student->full_name}).",
            'data' => [
                'id' => $mealLog->id,
                'student_name' => $student->full_name,
                'dining_number' => $student->getDiningNumberFormatted(),
                'jenis_makan' => ucfirst($mealTime),
                'tanggal' => Carbon::parse($today)->format('d M Y'),
                'waktu' => now()->format('H:i') . ' WIB',
            ],
        ]);
    }

    /**
     * GET /api/staff/dining/logs
     * Daftar seluruh presensi makan yang diinput oleh staff hari ini.
     */
    public function getStaffTodayLogs(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $logs = MealLog::with('student')
            ->whereDate('date_consumed', $today)
            ->orderBy('scanned_at', 'desc')
            ->get();

        $formatted = $logs->map(function ($log, $index) {
            $scanned = Carbon::parse($log->scanned_at);
            return [
                'no' => $index + 1,
                'id' => $log->id,
                'dining_number' => $log->student ? $log->student->getDiningNumberFormatted() : str_pad((string)$log->eating_number, 3, '0', STR_PAD_LEFT),
                'student_name' => $log->student?->full_name ?? 'Siswa',
                'tanggal' => Carbon::parse($log->date_consumed)->format('d M Y'),
                'waktu' => $scanned->format('H:i') . ' WIB',
                'jenis_makan' => ucfirst(strtolower($log->meal_time)),
            ];
        });

        return response()->json([
            'today' => Carbon::parse($today)->format('d M Y'),
            'total_input_today' => count($formatted),
            'logs' => $formatted,
        ]);
    }
}
