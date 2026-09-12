<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan akun sihotang@gmail.com (Sahat Sihotang) masuk router guru.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'sihotang@gmail.com')
            ->update([
                'role' => 'guru',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse: kembalikan ke siswa (jika perlu rollback).
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', 'sihotang@gmail.com')
            ->update([
                'role' => 'siswa',
                'updated_at' => now(),
            ]);
    }
};
