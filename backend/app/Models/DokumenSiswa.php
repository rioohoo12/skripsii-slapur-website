<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSiswa extends Model
{
    protected $table = 'dokumen_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_dokumen_id',
        'file_path',
        'status',
        'catatan',
        'diverifikasi_oleh',
        'diverifikasi_at',
    ];

    protected $casts = [
        'diverifikasi_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumen::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
