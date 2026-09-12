<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormRoom extends Model
{
    protected $table = 'dorm_rooms';

    protected $fillable = [
        'building_id',
        'dorm_type_id',
        'room_number',
        'capacity',
        'current_occupancy',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(DormBuilding::class, 'building_id');
    }

    public function dormType(): BelongsTo
    {
        return $this->belongsTo(DormType::class, 'dorm_type_id');
    }

    public function assignedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'assigned_room_id');
    }

    public function isAvailable(): bool
    {
        return $this->current_occupancy < $this->capacity;
    }

    public function getAvailableSlots(): int
    {
        return $this->capacity - $this->current_occupancy;
    }
}
