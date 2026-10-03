<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruAttendance extends Model
{
    protected $table = 'guru_attendances';

    protected $fillable = [
        'guru_id',
        'siswa_id',
        'tingkat',
        'subject_name',
        'date',
        'status',
        'remarks',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
