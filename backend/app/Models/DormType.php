<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormType extends Model
{
    protected $table = 'dorm_types';

    protected $fillable = [
        'type_name',
        'cost',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(DormRoom::class, 'dorm_type_id');
    }
}
