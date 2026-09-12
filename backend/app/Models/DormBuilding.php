<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormBuilding extends Model
{
    protected $table = 'dorm_buildings';

    protected $fillable = [
        'building_name',
        'gender_allowance',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(DormRoom::class, 'building_id');
    }
}
