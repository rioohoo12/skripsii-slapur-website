<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompleteDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles with complete permissions
        $this->seedRoles();

        // 2. Seed Users (Super Admin, Staff, Guru, and sample Students)
        $this->seedUsers();

        // 3. Seed Dorm Data
        $this->seedDorms();

        // 4. Seed Students with registration progress
        $this->seedStudents();

        // 5. Seed Classes and Academic Year
        $this->seedAcademicData();

        // 6. Seed Cafeteria Menus
        $this->seedCafeteriaMenus();

        // 7. Seed Sample Attendance, Grades, and Meal Logs
        $this->seedAcademicLogs();
    }

    private function seedRoles(): void
    {
        $roles = [
            [
                'role_name' => 'Super Admin',
                'description' => 'Admin penuh, kelola semua aspek',
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
                'can_access_student_dashboard' => true,
                'can_choose_dorm_room' => true,
            ],
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
                'role_name' => 'Staff Asrama',
                'description' => 'Staff asrama, manage kamar & kunci',
                'can_manage_users' => false,
                'can_manage_settings' => false,
                'can_input_grades' => false,
                'can_view_grades' => false,
                'can_input_attendance' => false,
                'can_manage_classes' => false,
                'can_scan_meal_card' => false,
                'can_manage_cafeteria_menu' => false,
                'can_manage_dorm_rooms' => true,
                'can_assign_student_room' => true,
                'can_manage_keys' => true,
                'can_verify_payments' => false,
                'can_verify_documents' => false,
                'can_access_student_dashboard' => false,
                'can_choose_dorm_room' => false,
            ],
            [
                'role_name' => 'Staff Kantin',
                'description' => 'Staff kafetaria, kelola menu & scan kartu makan',
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
                'role_name' => 'Administrasi',
                'description' => 'Staff administrasi, verifikasi pembayaran & dokumen',
                'can_manage_users' => false,
                'can_manage_settings' => false,
                'can_input_grades' => false,
                'can_view_grades' => false,
                'can_input_attendance' => false,
                'can_manage_classes' => false,
                'can_scan_meal_card' => false,
                'can_manage_cafeteria_menu' => false,
                'can_manage_dorm_rooms' => false,
                'can_assign_student_room' => false,
                'can_manage_keys' => false,
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

    private function seedUsers(): void
    {
        // Clear existing users (except default ones)
        DB::table('users')->where('email', 'like', 'test%')->delete();

        // Get role IDs
        $superAdminRole = DB::table('roles')->where('role_name', 'Super Admin')->first();
        $guruRole = DB::table('roles')->where('role_name', 'Guru')->first();
        $muridRole = DB::table('roles')->where('role_name', 'Murid')->first();
        $staffAsramaRole = DB::table('roles')->where('role_name', 'Staff Asrama')->first();
        $staffKantinRole = DB::table('roles')->where('role_name', 'Staff Kantin')->first();
        $administrasiRole = DB::table('roles')->where('role_name', 'Administrasi')->first();

        // Super Admin
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@sekolah.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@sekolah.com',
                'password' => Hash::make('password123'),
                'role' => 'Super Admin',
                'role_id' => $superAdminRole->id,
                'is_verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Gurus
        $gurus = [
            ['name' => 'Bu Siti Nurhaliza', 'email' => 'test.guru1@sekolah.com', 'jenis_kelamin' => 'P'],
            ['name' => 'Pak Ahmad Ridho', 'email' => 'test.guru2@sekolah.com', 'jenis_kelamin' => 'L'],
            ['name' => 'Bu Eka Widhiastuti', 'email' => 'test.guru3@sekolah.com', 'jenis_kelamin' => 'P'],
        ];

        foreach ($gurus as $guru) {
            DB::table('users')->updateOrInsert(
                ['email' => $guru['email']],
                [
                    'name' => $guru['name'],
                    'email' => $guru['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'Guru',
                    'role_id' => $guruRole->id,
                    'jenis_kelamin' => $guru['jenis_kelamin'],
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Staff Asrama
        DB::table('users')->updateOrInsert(
            ['email' => 'test.staff.asrama@sekolah.com'],
            [
                'name' => 'Staff Asrama',
                'email' => 'asrama@gmail.com',
                'password' => Hash::make('Slapur123'),
                'role' => 'Staff Asrama',
                'role_id' => $staffAsramaRole->id,
                'jenis_kelamin' => 'L',
                'is_verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Staff Kantin
        DB::table('users')->updateOrInsert(
            ['email' => 'test.staff.kantin@sekolah.com'],
            [
                'name' => 'Pak Suryanto',
                'email' => 'test.staff.kantin@sekolah.com',
                'password' => Hash::make('password123'),
                'role' => 'Staff Kantin',
                'role_id' => $staffKantinRole->id,
                'jenis_kelamin' => 'L',
                'is_verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Staff Kafetaria (Dining)
        DB::table('users')->updateOrInsert(
            ['email' => 'kafetaria@gmail.com'],
            [
                'name' => 'Staff Kafetaria',
                'email' => 'kafetaria@gmail.com',
                'password' => Hash::make('Kafetaria123'),
                'role' => 'staff_kafetaria',
                'role_id' => $staffKantinRole->id, // Fallback ke role ID staf kantin jika belum ada spesifik
                'jenis_kelamin' => 'Laki-laki',
                'is_verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Administrasi
        DB::table('users')->updateOrInsert(
            ['email' => 'test.administrasi@sekolah.com'],
            [
                'name' => 'Bu Dewi Masitro',
                'email' => 'test.administrasi@sekolah.com',
                'password' => Hash::make('password123'),
                'role' => 'Administrasi',
                'role_id' => $administrasiRole->id,
                'jenis_kelamin' => 'P',
                'is_verified' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Sample Students
        $students = [
            ['name' => 'Aldi Pratama', 'email' => 'test.siswa1@sekolah.com', 'jenis_kelamin' => 'L'],
            ['name' => 'Siti Anisa', 'email' => 'test.siswa2@sekolah.com', 'jenis_kelamin' => 'P'],
            ['name' => 'Budi Santoso', 'email' => 'test.siswa3@sekolah.com', 'jenis_kelamin' => 'L'],
            ['name' => 'Ratna Dewi', 'email' => 'test.siswa4@sekolah.com', 'jenis_kelamin' => 'P'],
            ['name' => 'Rifai Muhtadi', 'email' => 'test.siswa5@sekolah.com', 'jenis_kelamin' => 'L'],
        ];

        foreach ($students as $student) {
            DB::table('users')->updateOrInsert(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'email' => $student['email'],
                    'password' => Hash::make('password123'),
                    'role' => 'Murid',
                    'role_id' => $muridRole->id,
                    'jenis_kelamin' => $student['jenis_kelamin'],
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedDorms(): void
    {
        // Create Dorm Types if they don't exist
        DB::table('dorm_types')->updateOrInsert(
            ['type_name' => 'Putri'],
            ['cost' => 1500000, 'created_at' => now(), 'updated_at' => now()]
        );
        $putriType = DB::table('dorm_types')->where('type_name', 'Putri')->first();

        DB::table('dorm_types')->updateOrInsert(
            ['type_name' => 'Putra'],
            ['cost' => 1500000, 'created_at' => now(), 'updated_at' => now()]
        );
        $putraType = DB::table('dorm_types')->where('type_name', 'Putra')->first();

        // Create Dorm Buildings
        DB::table('dorm_buildings')->updateOrInsert(
            ['building_name' => 'Asrama Putri A'],
            ['gender_allowance' => 'P', 'created_at' => now(), 'updated_at' => now()]
        );
        $buildingPutri = DB::table('dorm_buildings')->where('building_name', 'Asrama Putri A')->first();

        DB::table('dorm_buildings')->updateOrInsert(
            ['building_name' => 'Asrama Putra A'],
            ['gender_allowance' => 'L', 'created_at' => now(), 'updated_at' => now()]
        );
        $buildingPutra = DB::table('dorm_buildings')->where('building_name', 'Asrama Putra A')->first();

        // Create Rooms for Putri
        $roomsPutri = [
            'P101', 'P102', 'P103', 'P104', 'P105',
            'P201', 'P202', 'P203', 'P204', 'P205',
        ];

        foreach ($roomsPutri as $roomNumber) {
            DB::table('dorm_rooms')->updateOrInsert(
                ['room_number' => $roomNumber],
                [
                    'building_id' => $buildingPutri->id,
                    'dorm_type_id' => $putriType->id,
                    'capacity' => 4,
                    'current_occupancy' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Create Rooms for Putra
        $roomsPutra = [
            'L101', 'L102', 'L103', 'L104', 'L105',
            'L201', 'L202', 'L203', 'L204', 'L205',
        ];

        foreach ($roomsPutra as $roomNumber) {
            DB::table('dorm_rooms')->updateOrInsert(
                ['room_number' => $roomNumber],
                [
                    'building_id' => $buildingPutra->id,
                    'dorm_type_id' => $putraType->id,
                    'capacity' => 4,
                    'current_occupancy' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedStudents(): void
    {
        // Get student user emails
        $studentEmails = [
            'test.siswa1@sekolah.com',
            'test.siswa2@sekolah.com',
            'test.siswa3@sekolah.com',
            'test.siswa4@sekolah.com',
            'test.siswa5@sekolah.com',
        ];

        $studentData = [
            ['nis' => '001', 'full_name' => 'Aldi Pratama', 'gender' => 'L', 'type' => 'Baru', 'boarding' => true],
            ['nis' => '002', 'full_name' => 'Siti Anisa', 'gender' => 'P', 'type' => 'Baru', 'boarding' => true],
            ['nis' => '003', 'full_name' => 'Budi Santoso', 'gender' => 'L', 'type' => 'Pindahan', 'boarding' => false],
            ['nis' => '004', 'full_name' => 'Ratna Dewi', 'gender' => 'P', 'type' => 'Baru', 'boarding' => true],
            ['nis' => '005', 'full_name' => 'Rifai Muhtadi', 'gender' => 'L', 'type' => 'Baru', 'boarding' => false],
        ];

        $roomsPutri = DB::table('dorm_rooms')->where('room_number', 'like', 'P%')->get();
        $roomsPutra = DB::table('dorm_rooms')->where('room_number', 'like', 'L%')->get();

        foreach ($studentEmails as $index => $email) {
            $user = DB::table('users')->where('email', $email)->first();
            $data = $studentData[$index];

            if ($user) {
                $roomId = null;
                if ($data['boarding']) {
                    $rooms = $data['gender'] === 'L' ? $roomsPutra : $roomsPutri;
                    if ($rooms->isNotEmpty()) {
                        $roomId = $rooms->random()->id;
                    }
                }

                DB::table('students')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'nis' => $data['nis'],
                        'full_name' => $data['full_name'],
                        'gender' => $data['gender'],
                        'is_transfer_student' => $data['type'] === 'Pindahan',
                        'previous_school_name' => $data['type'] === 'Pindahan' ? 'SMP Negeri 1 Kota' : null,
                        'is_boarder' => $data['boarding'],
                        'assigned_room_id' => $roomId,
                        'has_paid_registration' => rand(0, 1),
                        'has_uploaded_docs' => rand(0, 1),
                        'has_chosen_dorm_type' => $data['boarding'],
                        'has_chosen_room' => $roomId !== null,
                        'has_picked_up_key' => $data['boarding'] && rand(0, 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedAcademicData(): void
    {
        // Create Academic Year
        DB::table('academic_years')->updateOrInsert(
            ['year_name' => '2024/2025'],
            [
                'semester' => 'Ganjil',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $academicYear = DB::table('academic_years')->where('year_name', '2024/2025')->first();

        // Create Subjects
        $subjects = [
            'Matematika' => 'MAT', 'Bahasa Indonesia' => 'BIN', 'Bahasa Inggris' => 'BIG',
            'Fisika' => 'FIS', 'Kimia' => 'KIM', 'Biologi' => 'BIO', 'Geografi' => 'GEO',
            'Sejarah' => 'SEJ', 'Pendidikan Jasmani' => 'PEN', 'Seni Budaya' => 'SEN'
        ];

        foreach ($subjects as $subjectName => $subjectCode) {
            DB::table('subjects')->updateOrInsert(
                ['subject_name' => $subjectName],
                [
                    'subject_code' => $subjectCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Get teacher
        $teacher = DB::table('users')->where('email', 'test.guru1@sekolah.com')->first();

        // Create Classes
        $classes = [
            ['class_name' => 'X-IPA-1', 'academic_year_id' => $academicYear->id, 'homeroom_teacher_id' => $teacher->id ?? null],
            ['class_name' => 'X-IPA-2', 'academic_year_id' => $academicYear->id, 'homeroom_teacher_id' => $teacher->id ?? null],
            ['class_name' => 'X-IPS-1', 'academic_year_id' => $academicYear->id, 'homeroom_teacher_id' => $teacher->id ?? null],
        ];

        foreach ($classes as $classData) {
            DB::table('classes')->updateOrInsert(
                ['class_name' => $classData['class_name']],
                array_merge($classData, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Assign students to classes
        $students = DB::table('students')->get();
        $classes = DB::table('classes')->get();

        foreach ($students as $student) {
            if ($classes->isNotEmpty()) {
                foreach ($classes->random(rand(1, 2)) as $class) {
                    DB::table('student_classes')->updateOrInsert(
                        ['student_id' => $student->id, 'class_id' => $class->id],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }

    private function seedCafeteriaMenus(): void
    {
        $today = now()->format('Y-m-d');

        $menus = [
            [
                'date_served' => $today,
                'meal_time' => 'Pagi',
                'menu_details' => 'Nasi, Telur Goreng, Sayur Bayam, Kerupuk',
            ],
            [
                'date_served' => $today,
                'meal_time' => 'Siang',
                'menu_details' => 'Nasi Goreng, Ayam Teriyaki, Timun, Kerupuk, Buah',
            ],
            [
                'date_served' => $today,
                'meal_time' => 'Sore',
                'menu_details' => 'Nasi Kuning, Perkedel, Sayur Wortel, Teh Manis',
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('cafeteria_menus')->updateOrInsert(
                ['date_served' => $menu['date_served'], 'meal_time' => $menu['meal_time']],
                array_merge($menu, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedAcademicLogs(): void
    {
        $students = DB::table('students')->get();
        $classes = DB::table('classes')->get();
        $subjects = DB::table('subjects')->get();
        $menus = DB::table('cafeteria_menus')->get();

        $today = now()->format('Y-m-d');

        // Seed Attendance
        if ($classes->isNotEmpty() && $students->isNotEmpty()) {
            $schedules = DB::table('class_schedules')->get();

            if ($schedules->isNotEmpty()) {
                foreach ($students as $student) {
                    foreach ($schedules->random(rand(1, 3)) as $schedule) {
                        DB::table('attendances')->updateOrInsert(
                            ['student_id' => $student->id, 'schedule_id' => $schedule->id, 'date' => $today],
                            [
                                'status' => collect(['Hadir', 'Izin', 'Sakit', 'Alpha'])->random(),
                                'remarks' => rand(0, 1) ? 'Catatan' : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }

        // Seed Grades
        if ($students->isNotEmpty() && $subjects->isNotEmpty()) {
            $academicYear = DB::table('academic_years')->first();

            foreach ($students->random(rand(2, 5)) as $student) {
                foreach ($subjects->random(rand(3, 6)) as $subject) {
                    DB::table('grades')->updateOrInsert(
                        [
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'grade_type' => collect(['Tugas', 'UH', 'UTS', 'UAS'])->random(),
                        ],
                        [
                            'academic_year_id' => $academicYear->id,
                            'score' => rand(60, 100),
                            'teacher_notes' => 'Bagus, terus tingkatkan',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // Seed Meal Logs
        if ($students->isNotEmpty() && $menus->isNotEmpty()) {
            foreach ($students->random(rand(2, 5)) as $student) {
                foreach ($menus->random(rand(1, 3)) as $menu) {
                    DB::table('cafeteria_logs')->updateOrInsert(
                        [
                            'student_id' => $student->id,
                            'date_consumed' => $menu->date_served,
                            'meal_time' => $menu->meal_time,
                        ],
                        [
                            'eating_number' => rand(1000, 9999),
                            'scanned_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
