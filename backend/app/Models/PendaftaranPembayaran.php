<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranPembayaran extends Model
{
    protected $table = 'pendaftaran_pembayaran';

    protected $fillable = [
        'user_id',
        'total_biaya',
        'jumlah_dibayar',
        'status',
        'verified_at',
        'bukti_path',
    ];

    protected $casts = [
        'total_biaya' => 'decimal:0',
        'jumlah_dibayar' => 'decimal:0',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
