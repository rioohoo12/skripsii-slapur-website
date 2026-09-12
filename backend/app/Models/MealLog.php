<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    protected $table = 'cafeteria_logs';

    protected $fillable = [
        'student_id',
        'menu_id',
        'date_consumed',
        'meal_time',
        'eating_number',
        'scanned_at',
    ];

    protected $casts = [
        'date_consumed' => 'date',
        'scanned_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CafeteriaMenu::class, 'menu_id');
    }
}
