<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'Murid',
                'description' => 'Siswa yang mengakses dashboard siswa',
                'can_manage_users' => false,
                'can_manage_settings' => false,
                'can_input_grades' => false,
                'can_view_grades' => true,
                'can_input_attendance' => false,
                'can_manage_classes' => false,
                'can_scan_meal_card' => false,
                'can_manage_cafeteria_menu' => false,
                'can_manage_dorm_rooms' => false,
                'can_assign_student_room' => false,
                'can_manage_keys' => false,
                'can_verify_payments' => false,
                'can_verify_documents' => false,
                'can_access_student_dashboard' => true,
                'can_choose_dorm_room' => true,
            ],
            [
                'role_name' => 'Guru',
                'description' => 'Guru pengajar, input nilai & absensi',
                'can_manage_users' => false,
                'can_manage_settings' => false,
                'can_input_grades' => true,
                'can_view_grades' => true,
                'can_input_attendance' => true,
                'can_manage_classes' => true,
                'can_scan_meal_card' => false,
                'can_manage_cafeteria_menu' => false,
                'can_manage_dorm_rooms' => false,
                'can_assign_student_room' => false,
                'can_manage_keys' => false,
                'can_verify_payments' => false,
                'can_verify_documents' => false,
                'can_access_student_dashboard' => false,
                'can_choose_dorm_room' => false,
            ],
            [
                'role_name' => 'Staff Kantin',
                'description' => 'Staff kafetaria, scan kartu makan & menu',
                'can_manage_users' => false,
                'can_manage_settings' => false,
                'can_input_grades' => false,
                'can_view_grades' => false,
                'can_input_attendance' => false,
                'can_manage_classes' => false,
                'can_scan_meal_card' => true,
                'can_manage_cafeteria_menu' => true,
                'can_manage_dorm_rooms' => false,
                'can_assign_student_room' => false,
                'can_manage_keys' => false,
                'can_verify_payments' => false,
                'can_verify_documents' => false,
                'can_access_student_dashboard' => false,
                'can_choose_dorm_room' => false,
            ],
            [
                'role_name' => 'Super Admin',
                'description' => 'Admin penuh, kelola user & pengaturan',
                'can_manage_users' => true,
                'can_manage_settings' => true,
                'can_input_grades' => true,
                'can_view_grades' => true,
                'can_input_attendance' => true,
                'can_manage_classes' => true,
                'can_scan_meal_card' => true,
                'can_manage_cafeteria_menu' => true,
                'can_manage_dorm_rooms' => true,
                'can_assign_student_room' => true,
                'can_manage_keys' => true,
                'can_verify_payments' => true,
                'can_verify_documents' => true,
                'can_access_student_dashboard' => false,
                'can_choose_dorm_room' => false,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['role_name' => $role['role_name']],
                array_merge($role, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
