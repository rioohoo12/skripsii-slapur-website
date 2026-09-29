<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendaftaranKamar extends Model
{
    protected $table = 'pendaftaran_kamar';

    protected $fillable = [
        'nomor_kamar',
        'kapasitas',
        'current_occupancy',
        'status_kondisi',
        'catatan_fasilitas',
    ];

    public function selections(): HasMany
    {
        return $this->hasMany(PendaftaranRoomSelection::class, 'pendaftaran_kamar_id');
    }

    public function isFull(): bool
    {
        return $this->current_occupancy >= $this->kapasitas;
    }
}
