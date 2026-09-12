<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilihKamar extends Model
{
    protected $table = 'pilih_kamar';

    protected $fillable = [
        'user_id',
        'kamar_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(PendaftaranKamar::class, 'kamar_id');
    }
}
