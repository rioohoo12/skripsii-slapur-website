<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\PendaftaranProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    // === GURU METHODS ===

    /**
     * Guru: Lihat daftar tugas yang telah dibuat
     */
    public function indexGuru(Request $request)
    {
        $guru_id = $request->user()->id;
        $assignments = Assignment::where('guru_id', $guru_id)
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['assignments' => $assignments]);
    }

    /**
     * Guru: Buat tugas baru
     */
    public function storeGuru(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $guru = $request->user();
        
        // Coba ambil mata pelajaran dari tabel jenjang yang dihubungkan ke guru
        $subject_name = 'Mata Pelajaran'; // default
        if ($guru->jenjang_guru === 'smp' && $guru->subjectSmp) {
            $subject_name = $guru->subjectSmp->subject_name;
        } else if ($guru->jenjang_guru === 'sma' && $guru->subjectSma) {
            $subject_name = $guru->subjectSma->subject_name;
        } else if ($guru->subjectSmp) {
            $subject_name = $guru->subjectSmp->subject_name;
        } else if ($guru->subjectSma) {
            $subject_name = $guru->subjectSma->subject_name;
        }

        try {
            $assignment = Assignment::create([
                'guru_id' => $guru->id,
                'tingkat' => $request->tingkat,
                'subject_name' => $subject_name,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
            ]);

            return response()->json([
                'message' => 'Tugas berhasil dibuat',
                'assignment' => $assignment
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menyimpan tugas: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Guru: Lihat detail submission dari satu tugas
     */
    public function submissions(Request $request, $id)
    {
        $assignment = Assignment::where('guru_id', $request->user()->id)->findOrFail($id);
        
        $submissions = AssignmentSubmission::with('siswa:id,name')
            ->where('assignment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'assignment' => $assignment,
            'submissions' => $submissions
        ]);
    }


    // === SISWA METHODS ===

    /**
     * Siswa: Lihat daftar tugas untuk kelasnya
     */
    public function indexSiswa(Request $request)
    {
        $siswa_id = $request->user()->id;
        
        // Cari tingkat kelas siswa
        $profile = PendaftaranProfile::where('user_id', $siswa_id)->first();
        if (!$profile) {
            return response()->json(['assignments' => []]);
        }
        
        $tingkat = $profile->kelas_yang_didaftar;
        
        $assignments = Assignment::with('guru:id,name')
            ->where('tingkat', $tingkat)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Ambil submission siswa ini
        $submissions = AssignmentSubmission::where('siswa_id', $siswa_id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');
            
        // Map assignments to include submission status
        $assignments = $assignments->map(function ($assignment) use ($submissions) {
            $assignment->submission = $submissions->get($assignment->id);
            return $assignment;
        });

        return response()->json([
            'tingkat' => $tingkat,
            'assignments' => $assignments
        ]);
    }

    /**
     * Siswa: Kumpulkan tugas
     */
    public function submitSiswa(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        
        $siswa_id = $request->user()->id;
        
        // Cek apakah tugas ada
        $assignment = Assignment::findOrFail($id);
        
        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $id,
                'siswa_id' => $siswa_id,
            ],
            [
                'content' => $request->content,
                'status' => 'submitted',
            ]
        );
        
        return response()->json([
            'message' => 'Tugas berhasil dikumpulkan',
            'submission' => $submission
        ]);
    }
}
