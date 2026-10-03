<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuruAttendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // For Guru: submit attendance
    public function storeGuru(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|integer',
            'subject_name' => 'required|string',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.siswa_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|string',
            'attendances.*.remarks' => 'nullable|string',
        ]);

        $guru_id = $request->user()->id;

        foreach ($request->attendances as $att) {
            GuruAttendance::updateOrCreate(
                [
                    'guru_id' => $guru_id,
                    'siswa_id' => $att['siswa_id'],
                    'tingkat' => $request->tingkat,
                    'date' => $request->date,
                ],
                [
                    'subject_name' => $request->subject_name,
                    'status' => $att['status'],
                    'remarks' => $att['remarks'] ?? null,
                ]
            );
        }

        return response()->json(['message' => 'Absensi berhasil disimpan']);
    }

    // For Guru: get attendance by date
    public function getGuruByDate(Request $request)
    {
        $guru_id = $request->user()->id;
        $date = $request->query('date');
        $tingkat = $request->query('tingkat');

        $attendances = GuruAttendance::where('guru_id', $guru_id)
            ->where('tingkat', $tingkat)
            ->where('date', $date)
            ->get();

        return response()->json(['attendances' => $attendances]);
    }

    // For Siswa: get attendance history
    public function getSiswa(Request $request)
    {
        $siswa_id = $request->user()->id;
        $attendances = GuruAttendance::where('siswa_id', $siswa_id)
            ->with('guru:id,name')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['attendances' => $attendances]);
    }
}
