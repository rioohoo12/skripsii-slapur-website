<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'academic_year_id',
        'class_name',
        'homeroom_teacher_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, StudentClass::class, 'class_id', 'id', 'id', 'student_id');
    }
}
