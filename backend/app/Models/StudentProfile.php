<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'kelas_didaftar',
        'jenjang',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
