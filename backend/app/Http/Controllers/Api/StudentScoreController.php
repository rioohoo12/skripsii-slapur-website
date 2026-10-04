<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentScore;
use App\Models\User;
use App\Models\PendaftaranProfile;
use App\Models\Subject;

class StudentScoreController extends Controller
{
    /**
     * Guru: List scores of students in a specific class
     */
    public function indexGuru(Request $request, $tingkat)
    {
        $guru = $request->user();
        $guru_id = $guru->id;
        $subject_name = 'Pelajaran';
        if ($guru->jenjang_guru === 'smp' && $guru->subjectSmp) {
            $subject_name = $guru->subjectSmp->subject_name;
        } else if ($guru->jenjang_guru === 'sma' && $guru->subjectSma) {
            $subject_name = $guru->subjectSma->subject_name;
        } else if ($guru->subject) {
            $subject_name = $guru->subject->subject_name;
        }

        // Get students in this tingkat
        $students = User::where('role', 'siswa')
            ->whereHas('pendaftaranProfile', function($q) use ($tingkat) {
                $q->where('kelas_yang_didaftar', $tingkat);
            })
            ->select('id', 'name')
            ->get();
            
        // Get existing scores
        $scores = StudentScore::where('guru_id', $guru_id)
            ->where('subject_name', $subject_name)
            ->where('tingkat', $tingkat)
            ->get()
            ->keyBy('siswa_id');
            
        // Fetch assignments and submissions
        $assignments = \App\Models\Assignment::where('guru_id', $guru_id)
            ->where('tingkat', $tingkat)
            ->get();
            
        $submissions = \App\Models\AssignmentSubmission::whereIn('assignment_id', $assignments->pluck('id'))->get();
            
        // Map students to include scores and dynamic assignments
        $result = $students->map(function ($student) use ($scores, $assignments, $submissions) {
            $score = $scores->get($student->id);
            
            $studentAssignments = [];
            foreach ($assignments as $task) {
                $sub = $submissions->where('assignment_id', $task->id)->where('siswa_id', $student->id)->first();
                $studentAssignments[] = [
                    'assignment_id' => $task->id,
                    'status' => $sub ? $sub->status : 'missing',
                    'grade' => $sub ? $sub->grade : null
                ];
            }
            
            return [
                'siswa_id' => $student->id,
                'name' => $student->name,
                'nilai_tugas' => $score ? $score->nilai_tugas : null,
                'nilai_quiz' => $score ? $score->nilai_quiz : null,
                'nilai_harian' => $score ? $score->nilai_harian : null,
                'nilai_mid' => $score ? $score->nilai_mid : null,
                'nilai_final' => $score ? $score->nilai_final : null,
                'assignments' => $studentAssignments
            ];
        });
        
        return response()->json([
            'subject_name' => $subject_name,
            'tingkat' => $tingkat,
            'assignments_list' => $assignments,
            'students' => $result
        ]);
    }
    
    /**
     * Guru: Save/Update scores for multiple students
     */
    public function saveGuru(Request $request, $tingkat)
    {
        $guru = $request->user();
        $guru_id = $guru->id;
        $subject_name = 'Pelajaran';
        
        if ($guru->jenjang_guru === 'smp' && $guru->subjectSmp) {
            $subject_name = $guru->subjectSmp->subject_name;
        } else if ($guru->jenjang_guru === 'sma' && $guru->subjectSma) {
            $subject_name = $guru->subjectSma->subject_name;
        } else if ($guru->subject) {
            $subject_name = $guru->subject->subject_name;
        }
        
        $request->validate([
            'scores' => 'required|array',
            'scores.*.siswa_id' => 'required|exists:users,id',
            'scores.*.nilai_tugas' => 'nullable|numeric|min:0|max:100',
            'scores.*.nilai_quiz' => 'nullable|numeric|min:0|max:100',
            'scores.*.nilai_harian' => 'nullable|numeric|min:0|max:100',
            'scores.*.nilai_mid' => 'nullable|numeric|min:0|max:100',
            'scores.*.nilai_final' => 'nullable|numeric|min:0|max:100',
        ]);
        
        foreach ($request->scores as $data) {
            $tugas_avg = $data['nilai_tugas'] ?? null;
            
            if (isset($data['assignments']) && is_array($data['assignments'])) {
                $totalGrade = 0;
                $count = 0;
                
                foreach ($data['assignments'] as $taskData) {
                    if (array_key_exists('grade', $taskData) && $taskData['grade'] !== null) {
                        $submission = \App\Models\AssignmentSubmission::where('assignment_id', $taskData['assignment_id'])
                            ->where('siswa_id', $data['siswa_id'])
                            ->first();
                            
                        if ($submission) {
                            $submission->grade = $taskData['grade'];
                            $submission->status = 'graded';
                            $submission->save();
                        } else {
                            \App\Models\AssignmentSubmission::create([
                                'assignment_id' => $taskData['assignment_id'],
                                'siswa_id' => $data['siswa_id'],
                                'status' => 'graded',
                                'grade' => $taskData['grade']
                            ]);
                        }
                        $totalGrade += $taskData['grade'];
                        $count++;
                    }
                }
                
                if ($count > 0) {
                    $tugas_avg = round($totalGrade / $count, 2);
                }
            }
            
            StudentScore::updateOrCreate(
                [
                    'siswa_id' => $data['siswa_id'],
                    'guru_id' => $guru_id,
                    'subject_name' => $subject_name,
                ],
                [
                    'tingkat' => $tingkat,
                    'nilai_tugas' => $tugas_avg,
                    'nilai_quiz' => $data['nilai_quiz'] ?? null,
                    'nilai_harian' => $data['nilai_harian'] ?? null,
                    'nilai_mid' => $data['nilai_mid'] ?? null,
                    'nilai_final' => $data['nilai_final'] ?? null,
                ]
            );
        }
        
        return response()->json(['message' => 'Nilai berhasil disimpan']);
    }

    /**
     * Siswa: See their own scores for all subjects
     */
    public function indexSiswa(Request $request)
    {
        $siswa_id = $request->user()->id;
        
        $profile = PendaftaranProfile::where('user_id', $siswa_id)->first();
        if (!$profile) {
            return response()->json(['scores' => []]);
        }
        
        $scores = StudentScore::with('guru:id,name')
            ->where('siswa_id', $siswa_id)
            ->where('tingkat', $profile->kelas_yang_didaftar)
            ->get();
            
        return response()->json([
            'tingkat' => $profile->kelas_yang_didaftar,
            'scores' => $scores
        ]);
    }
}
