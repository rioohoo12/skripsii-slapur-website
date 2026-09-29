<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CafeteriaMenu;
use App\Models\MealLog;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KafetariaController extends Controller
{
    /**
     * GET /api/kafetaria/menus - Get all menus or filter by date
     */
    public function getMenus(Request $request): JsonResponse
    {
        $query = CafeteriaMenu::orderBy('date_served', 'desc')->orderBy('meal_time', 'asc');
        
        if ($request->has('date')) {
            $query->whereDate('date_served', $request->query('date'));
        }

        $menus = $query->get();
        return response()->json(['menus' => $menus]);
    }

    /**
     * POST /api/kafetaria/menus - Create a new menu
     */
    public function storeMenu(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_served' => 'required|date',
            'meal_time' => 'required|in:Pagi,Siang,Sore',
            'menu_details' => 'required|string',
        ]);

        // Cek apakah menu untuk tanggal dan waktu ini sudah ada
        $exists = CafeteriaMenu::where('date_served', $validated['date_served'])
            ->where('meal_time', $validated['meal_time'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Menu untuk waktu tersebut sudah ada pada tanggal ini.'], 422);
        }

        $menu = CafeteriaMenu::create($validated);
        return response()->json(['message' => 'Menu berhasil ditambahkan.', 'menu' => $menu], 201);
    }

    /**
     * PUT /api/kafetaria/menus/{id} - Update a menu
     */
    public function updateMenu(Request $request, $id): JsonResponse
    {
        $menu = CafeteriaMenu::find($id);
        if (!$menu) {
            return response()->json(['message' => 'Menu tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'date_served' => 'required|date',
            'meal_time' => 'required|in:Pagi,Siang,Sore',
            'menu_details' => 'required|string',
        ]);

        $menu->update($validated);
        return response()->json(['message' => 'Menu berhasil diperbarui.', 'menu' => $menu]);
    }

    /**
     * DELETE /api/kafetaria/menus/{id} - Delete a menu
     */
    public function destroyMenu($id): JsonResponse
    {
        $menu = CafeteriaMenu::find($id);
        if (!$menu) {
            return response()->json(['message' => 'Menu tidak ditemukan.'], 404);
        }
        $menu->delete();
        return response()->json(['message' => 'Menu berhasil dihapus.']);
    }

    /**
     * GET /api/kafetaria/today - Get today's menus and check if current student has consumed them
     */
    public function todayMenu(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $menus = CafeteriaMenu::whereDate('date_served', $today)->get();
        
        $user = $request->user();
        $student = $user ? Student::where('user_id', $user->id)->first() : null;
        
        $consumed = [];
        if ($student) {
            $logs = MealLog::where('student_id', $student->id)
                ->whereDate('date_consumed', $today)
                ->get();
            $consumed = $logs->pluck('meal_time')->toArray();
        }

        return response()->json([
            'date' => $today,
            'menus' => $menus,
            'consumed' => $consumed,
            'dining_number' => $student ? $student->getDiningNumberFormatted() : null,
            'student_name' => $student ? $student->full_name : null,
        ]);
    }

    /**
     * POST /api/kafetaria/scan - Verifikasi Presensi Makan Siswa
     */
    public function scanQrCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dining_number' => 'required|string',
            'meal_time' => 'required|in:Pagi,Siang,Sore',
        ]);

        $today = Carbon::today()->toDateString();
        $diningNumber = $validated['dining_number'];
        $mealTime = $validated['meal_time'];

        $rawNumber = (string)(int)$diningNumber;
        $paddedNumber4 = str_pad($rawNumber, 4, '0', STR_PAD_LEFT);
        $paddedNumber3 = str_pad($rawNumber, 3, '0', STR_PAD_LEFT);

        $student = Student::where('dining_number', $diningNumber)
            ->orWhere('dining_number', $rawNumber)
            ->orWhere('nomor_pendaftaran', 'LIKE', '%-' . $paddedNumber4)
            ->orWhere('nomor_pendaftaran', 'LIKE', '%-' . $paddedNumber3)
            ->orWhere('nomor_pendaftaran', 'LIKE', '%-' . $diningNumber)
            ->first();
            
        if (!$student) {
            return response()->json(['message' => 'QR Code tidak valid atau siswa tidak ditemukan.'], 404);
        }

        $menu = CafeteriaMenu::whereDate('date_served', $today)->where('meal_time', $mealTime)->first();
        if (!$menu) {
            return response()->json(['message' => 'Menu untuk sesi ' . $mealTime . ' hari ini belum diatur.'], 404);
        }

        // Validasi double claim
        $alreadyConsumed = MealLog::where('student_id', $student->id)
            ->whereDate('date_consumed', $today)
            ->where('meal_time', $mealTime)
            ->exists();

        if ($alreadyConsumed) {
            return response()->json([
                'message' => 'Siswa sudah melakukan presensi makan untuk sesi ' . $mealTime . ' hari ini.',
                'student' => ['name' => $student->full_name]
            ], 422);
        }

        // Catat presensi
        $log = MealLog::create([
            'student_id' => $student->id,
            'date_consumed' => $today,
            'meal_time' => $mealTime,
            'eating_number' => $rawNumber,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'message' => 'Presensi berhasil. Silakan makan.',
            'student' => [
                'name' => $student->full_name,
                'dining_number' => $student->dining_number
            ],
            'meal_time' => $mealTime
        ]);
    }

    /**
     * GET /api/kafetaria/laporan - Laporan kehadiran dining (Staff Kafetaria)
     */
    public function report(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        
        $logs = MealLog::with('student')
            ->whereDate('date_consumed', $date)
            ->orderBy('scanned_at', 'desc')
            ->get();
            
        $summary = [
            'Pagi' => 0,
            'Siang' => 0,
            'Sore' => 0,
            'Total' => $logs->count(),
        ];
        
        foreach ($logs as $log) {
            if (isset($summary[$log->meal_time])) {
                $summary[$log->meal_time]++;
            }
        }

        return response()->json([
            'date' => $date,
            'summary' => $summary,
            'logs' => $logs->map(fn($log) => [
                'id' => $log->id,
                'student_name' => $log->student->full_name ?? 'Siswa',
                'meal_time' => $log->meal_time,
                'scanned_at' => $log->scanned_at->format('H:i:s'),
            ])
        ]);
    }

    /**
     * GET /api/kafetaria/dashboard - Combined data for dashboard
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        
        // 1. Get Menus
        $menus = CafeteriaMenu::whereDate('date_served', $today)->get();
        
        // 2. Get Summary
        $logs = MealLog::whereDate('date_consumed', $today)->get(['meal_time']);
        $summary = [
            'Pagi' => 0,
            'Siang' => 0,
            'Sore' => 0,
            'Total' => $logs->count(),
        ];
        
        foreach ($logs as $log) {
            if (isset($summary[$log->meal_time])) {
                $summary[$log->meal_time]++;
            }
        }

        return response()->json([
            'menus' => $menus,
            'summary' => $summary
        ]);
    }
}
