<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /**
     * Tabel yang digunakan model ini.
     *
     * Mengikuti migration 2025_02_26_100009_create_academic_tables:
     * - nama kolom: subject_name, subject_code
     */
    protected $table = 'subjects';

    protected $fillable = [
        'subject_name',
        'subject_code',
        'jenjang',
    ];

    public function scopeForJenjang($query, string $jenjang)
    {
        if ($jenjang === 'smp') {
            return $query->whereIn('jenjang', ['smp', 'umum']);
        }
        if ($jenjang === 'sma') {
            return $query->whereIn('jenjang', ['sma', 'umum']);
        }
        return $query;
    }
}
