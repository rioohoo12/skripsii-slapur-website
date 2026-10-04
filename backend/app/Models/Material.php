<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'guru_id',
        'tingkat',
        'subject_name',
        'title',
        'type',
        'file_url'
    ];
    
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
