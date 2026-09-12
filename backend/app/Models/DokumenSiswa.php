<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSiswa extends Model
{
    protected $table = 'dokumen_siswa';

    protected $fillable = [
        'user_id',
        'jenis',
        'file_path',
        'file_name',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
