<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranRoomSelection extends Model
{
    protected $table = 'pendaftaran_room_selections';

    protected $fillable = [
        'user_id',
        'pendaftaran_kamar_id',
        'status',
        'approved_at',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(PendaftaranKamar::class, 'pendaftaran_kamar_id');
    }
}
