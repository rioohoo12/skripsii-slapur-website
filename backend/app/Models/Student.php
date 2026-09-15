<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'nis',
        'full_name',
        'gender',
        'is_transfer_student',
        'previous_school_name',
        'is_boarder',
        'assigned_room_id',
        'has_paid_registration',
        'has_uploaded_docs',
        'has_chosen_dorm_type',
        'has_chosen_room',
        'has_picked_up_key',
        'nomor_pendaftaran',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'no_telp_ortu',
        'status_pendaftaran',
        'dining_number',
        'dining_status',
    ];

    protected $casts = [
        'is_transfer_student' => 'boolean',
        'is_boarder' => 'boolean',
        'has_paid_registration' => 'boolean',
        'has_uploaded_docs' => 'boolean',
        'has_chosen_dorm_type' => 'boolean',
        'has_chosen_room' => 'boolean',
        'has_picked_up_key' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedRoom(): BelongsTo
    {
        return $this->belongsTo(DormRoom::class, 'assigned_room_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class, 'student_id');
    }

    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'student_id');
    }

    /**
     * Check if student has completed registration steps
     */
    public function isRegistrationComplete(): bool
    {
        return $this->has_paid_registration 
            && $this->has_uploaded_docs 
            && (!$this->is_boarder || ($this->has_chosen_dorm_type && $this->has_chosen_room && $this->has_picked_up_key));
    }

    /**
     * Get registration progress percentage
     */
    public function getRegistrationProgress(): int
    {
        $steps = 2; // payment + docs
        $completed = ($this->has_paid_registration ? 1 : 0) + ($this->has_uploaded_docs ? 1 : 0);

        if ($this->is_boarder) {
            $steps += 3; // dorm_type + room + key
            $completed += ($this->has_chosen_dorm_type ? 1 : 0) + ($this->has_chosen_room ? 1 : 0) + ($this->has_picked_up_key ? 1 : 0);
        }

        return (int)(($completed / $steps) * 100);
    }

    /**
     * Get or auto-generate dining number based on registration sequence (e.g., 001, 002)
     */
    public function getDiningNumberFormatted(): string
    {
        if (!empty($this->dining_number)) {
            return str_pad($this->dining_number, 3, '0', STR_PAD_LEFT);
        }

        if (!empty($this->nomor_pendaftaran)) {
            $parts = explode('-', $this->nomor_pendaftaran);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                return str_pad($lastPart, 3, '0', STR_PAD_LEFT);
            }
        }

        return str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
