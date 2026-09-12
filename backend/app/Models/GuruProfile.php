<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruProfile extends Model
{
    protected $fillable = ['user_id', 'nip', 'no_hp'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
