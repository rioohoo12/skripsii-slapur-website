<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranPayment extends Model
{
    protected $table = 'pendaftaran_payments';

    protected $fillable = [
        'user_id',
        'total_tagihan',
        'nominal_60_percent',
        'nominal_dibayar',
        'status',
        'verified_at',
        'bukti_path',
    ];

    protected $casts = [
        'total_tagihan' => 'decimal:2',
        'nominal_60_percent' => 'decimal:2',
        'nominal_dibayar' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
