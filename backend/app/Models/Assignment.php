<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'tingkat',
        'subject_name',
        'title',
        'description',
        'due_date',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
