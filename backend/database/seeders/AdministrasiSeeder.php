<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\JenisDokumen;

class AdministrasiSeeder extends Seeder
{
    public function run()
    {
        // 1. Setup jenis dokumen master
        $dokumens = [
            ['nama' => 'Kartu Keluarga (KK)', 'wajib' => true, 'format' => 'PDF, JPG'],
            ['nama' => 'Akta Kelahiran', 'wajib' => true, 'format' => 'PDF, JPG'],
            ['nama' => 'Rapor Semester 1-5', 'wajib' => true, 'format' => 'PDF'],
            ['nama' => 'Pas Foto 3x4', 'wajib' => true, 'format' => 'JPG, PNG'],
            ['nama' => 'Sertifikat Prestasi', 'wajib' => false, 'format' => 'PDF, JPG'],
        ];

        foreach ($dokumens as $dok) {
            JenisDokumen::updateOrCreate(['nama' => $dok['nama']], $dok);
        }

        // 2. Setup user staff administrasi jika belum ada
        User::updateOrCreate(
            ['email' => 'admin.sekolah@sekolah.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'jenis_kelamin' => 'laki-laki'
            ]
        );
    }
}
