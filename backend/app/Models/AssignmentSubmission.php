<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'siswa_id',
        'content',
        'file_path',
        'status',
        'grade',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
