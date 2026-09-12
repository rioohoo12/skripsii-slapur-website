<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaranTingkat extends Model
{
    protected $table = 'mata_pelajaran_tingkat';

    protected $fillable = [
        'tingkat',
        'nama_pelajaran',
        'urutan',
    ];
}
