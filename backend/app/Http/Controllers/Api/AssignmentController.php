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
            ->get()
            ->map(function ($sub) {
                if ($sub->file_path) {
                    $sub->file_url = url('storage/' . $sub->file_path);
                }
                return $sub;
            });
            
        return response()->json([
            'assignment' => $assignment,
            'submissions' => $submissions
        ]);
    }

    /**
     * Guru: Beri nilai untuk submission
     */
    public function gradeSubmissions(Request $request, $id)
    {
        $guru_id = $request->user()->id;
        $assignment = Assignment::where('guru_id', $guru_id)->findOrFail($id);
        
        $request->validate([
            'grades' => 'required|array',
            'grades.*.submission_id' => 'required|exists:assignment_submissions,id',
            'grades.*.grade' => 'nullable|numeric|min:0|max:100',
        ]);
        
        $subject = \App\Models\Subject::find($request->user()->subject_id);
        $subject_name = $subject ? $subject->subject_name : 'Pelajaran';
        
        foreach ($request->grades as $data) {
            $submission = AssignmentSubmission::where('assignment_id', $id)
                ->where('id', $data['submission_id'])
                ->first();
                
            if ($submission && $data['grade'] !== null) {
                $submission->grade = $data['grade'];
                $submission->status = 'graded';
                $submission->save();
                
                // Sinkronisasi otomatis ke tabel student_scores (Nilai Tugas)
                \App\Models\StudentScore::updateOrCreate(
                    [
                        'siswa_id' => $submission->siswa_id,
                        'guru_id' => $guru_id,
                        'subject_name' => $subject_name,
                    ],
                    [
                        'tingkat' => $assignment->tingkat,
                        'nilai_tugas' => $data['grade'],
                    ]
                );
            }
        }
        
        return response()->json(['message' => 'Nilai berhasil disimpan dan disinkronkan']);
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
            
        // Map assignments to include submission status and file URL
        $assignments = $assignments->map(function ($assignment) use ($submissions) {
            $sub = $submissions->get($assignment->id);
            if ($sub && $sub->file_path) {
                $sub->file_url = url('storage/' . $sub->file_path);
            }
            $assignment->submission = $sub;
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
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Max 10MB
        ]);
        
        // At least one must be present
        if (empty($request->content) && !$request->hasFile('file')) {
            return response()->json(['message' => 'Konten atau file wajib diisi'], 400);
        }
        
        $siswa_id = $request->user()->id;
        
        // Cek apakah tugas ada
        $assignment = Assignment::findOrFail($id);
        
        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('assignments', 'public');
        }
        
        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('siswa_id', $siswa_id)
            ->first();
            
        if ($submission) {
            $submission->content = $request->content;
            if ($filePath) {
                $submission->file_path = $filePath;
                $submission->file_name = $fileName;
            }
            $submission->save();
        } else {
            $submission = AssignmentSubmission::create([
                'assignment_id' => $id,
                'siswa_id' => $siswa_id,
                'content' => $request->content,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'status' => 'submitted',
            ]);
        }
        
        return response()->json([
            'message' => 'Tugas berhasil dikumpulkan',
            'submission' => $submission
        ]);
    }
}
