<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $fillable = [
        'user_id',
        'jenis',
        'file_path',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public const JENIS_AKTE = 'akte_kelahiran';
    public const JENIS_KK = 'kk';
    public const JENIS_PAS_FOTO = 'pas_foto';
    public const JENIS_RAPORT = 'raport';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
