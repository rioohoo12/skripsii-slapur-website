<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pindahkan akun Sahat Sihotang dari role siswa ke guru agar masuk router guru.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('users')
            ->where(function ($q) {
                $q->where('name', 'like', '%Sahat%Sihotang%')
                  ->orWhere('name', 'like', '%Sihotang%Sahat%');
            })
            ->update([
                'role' => 'guru',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse: kembalikan role ke siswa (jika perlu rollback).
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('users')
            ->where('name', 'like', '%Sahat%Sihotang%')
            ->orWhere('name', 'like', '%Sihotang%Sahat%')
            ->update([
                'role' => 'siswa',
                'updated_at' => now(),
            ]);
    }
};
