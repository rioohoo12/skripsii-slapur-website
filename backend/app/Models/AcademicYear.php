<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $table = 'academic_years';

    protected $fillable = [
        'year_name',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(Classes::class, 'academic_year_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'academic_year_id');
    }

    public const SEMESTERS = [
        'Ganjil' => 'Semester Ganjil',
        'Genap' => 'Semester Genap',
    ];
}
