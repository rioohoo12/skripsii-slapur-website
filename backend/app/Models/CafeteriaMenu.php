<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeteriaMenu extends Model
{
    protected $table = 'cafeteria_menus';

    protected $fillable = [
        'date_served',
        'meal_time',
        'menu_details',
    ];

    protected $casts = [
        'date_served' => 'date',
    ];

    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class, 'menu_id');
    }

    public const MEAL_TIMES = [
        'Pagi' => 'Sarapan (Pagi)',
        'Siang' => 'Makan Siang',
        'Sore' => 'Makan Sore',
    ];
}
