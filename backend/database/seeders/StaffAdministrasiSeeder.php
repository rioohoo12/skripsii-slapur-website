<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StaffAdministrasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'administrasi@gmail.com'],
            [
                'name' => 'Staff Administrasi',
                'password' => Hash::make('Administrasislapur123'),
                'role' => 'staff',
                'status' => 'aktif',
            ]
        );
    }
}
