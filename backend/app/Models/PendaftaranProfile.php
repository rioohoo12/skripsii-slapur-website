<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranProfile extends Model
{
    protected $table = 'pendaftaran_profiles';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'no_hp',
        'alamat',
        'kelas_yang_didaftar',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
