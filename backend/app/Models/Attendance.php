<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'student_id',
        'schedule_id',
        'date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public const STATUSES = [
        'Hadir' => 'Hadir',
        'Izin' => 'Izin',
        'Sakit' => 'Sakit',
        'Alpha' => 'Alpa (Alpha)',
    ];

    public function isPresent(): bool
    {
        return $this->status === 'Hadir';
    }
}
