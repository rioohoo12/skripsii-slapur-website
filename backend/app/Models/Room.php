<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk kamar asrama (tabel dorm_rooms).
 * Dipakai oleh RoomAssignment sebagai relasi room().
 */
class Room extends Model
{
    protected $table = 'dorm_rooms';

    protected $fillable = [
        'building_id',
        'dorm_type_id',
        'room_number',
        'capacity',
        'current_occupancy',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class);
    }
}

