<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $table = 'grades';

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'grade_type',
        'score',
        'teacher_notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public const GRADE_TYPES = [
        'Tugas' => 'Tugas',
        'UH' => 'Ulangan Harian',
        'UTS' => 'Ujian Tengah Semester',
        'UAS' => 'Ujian Akhir Semester',
    ];

    public function getGradeLabel(): string
    {
        return $this->score >= 70 ? 'Tuntas' : 'Belum Tuntas';
    }
}
