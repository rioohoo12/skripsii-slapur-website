<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'jenis', 'nominal', 'jatuh_tempo', 'status'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'siswa_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
