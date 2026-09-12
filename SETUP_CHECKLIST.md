# ✅ Database Implementation Checklist

> 🚀 **Menggunakan Supabase (PostgreSQL Cloud)**  
> Untuk panduan lengkap: [SUPABASE_SETUP_GUIDE.md](SUPABASE_SETUP_GUIDE.md)

## 📋 What Has Been Implemented

### ✅ Database Migrations (15 tables)

- [x] roles - Role dan permission management
- [x] users - User login (updated dengan is_verified & role_id)
- [x] students - Data siswa dengan status registrasi
- [x] dorm_types - Tipe asrama
- [x] dorm_buildings - Gedung asrama
- [x] dorm_rooms - Ruangan asrama
- [x] academic_years - Tahun akademik
- [x] classes - Kelas pelajaran
- [x] subjects - Mata pelajaran
- [x] student_classes - Hubungan siswa ke kelas
- [x] class_schedules - Jadwal pelajaran
- [x] attendances - Absensi siswa
- [x] grades - Nilai siswa
- [x] cafeteria_menus - Menu kafetaria
- [x] cafeteria_logs - Log pemindaian makan

### ✅ Eloquent Models (15 models)

```
✓ User (updated)
✓ Role (created)
✓ Student
✓ DormType
✓ DormBuilding
✓ DormRoom
✓ AcademicYear
✓ Classes
✓ StudentClass
✓ ClassSchedule
✓ Subject
✓ Attendance
✓ Grade
✓ CafeteriaMenu
✓ MealLog
```

### ✅ Seeders

- [x] RoleSeeder (existing) - 6 roles dengan permissions
- [x] CompleteDataSeeder (new) - Full sample data:
  - 1 Super Admin
  - 3 Gurus
  - 1 Staff Asrama
  - 1 Staff Kantin
  - 1 Administrasi
  - 5 Siswa
  - 20 Kamar (10 Putri, 10 Putra)
  - 10 Mata pelajaran
  - 3 Kelas
  - Sample: Attendance, Grades, Meal Logs

### ✅ Database Seeder Integration

- [x] DatabaseSeeder updated untuk include CompleteDataSeeder

### ✅ Models dan Relationships

- [x] User → Role (belongsTo)
- [x] Student → User (belongsTo)
- [x] Student → DormRoom (belongsTo)
- [x] Student → Classes (hasManyThrough StudentClass)
- [x] Student → Attendances (hasMany)
- [x] Student → Grades (hasMany)
- [x] Student → MealLogs (hasMany)
- [x] Classes → AcademicYear (belongsTo)
- [x] Classes → User (belongsTo as homeroomTeacher)
- [x] ClassSchedule → Classes, Subject, User
- [x] DormRoom → DormType, DormBuilding
- [x] Grade → Student, Subject, AcademicYear, User
- [x] Attendance → Student, ClassSchedule
- [x] MealLog → Student, CafeteriaMenu

### ✅ Documentation

- [x] DATABASE_SETUP_GUIDE.md - Complete setup instructions
- [x] API_ROUTES_REFERENCE.php - API endpoints to implement
- [x] SETUP_CHECKLIST.md (this file)

---

## 🚀 Quick Start Steps (Menggunakan Supabase)

### Step 1: Create Supabase Project

1. Buka https://app.supabase.com dan login/daftar
2. Klik **"New Project"**
3. Isi form:
   - **Project name**: `skrippsii`
   - **Database password**: Simpan dengan aman!
   - **Region**: Indonesia atau Asia terdekat
4. Tunggu project selesai dibuat (~2 menit)

### Step 2: Get Connection Credentials

1. Buka **Project Settings** → **Database**
2. Copy informasi berikut:
   - Host (e.g., `db.xxxxxxxxxxxxx.supabase.co`)
   - Database: `postgres`
   - Username: `postgres`
   - Password: Password yang dibuat di Step 1

### Step 3: Update .env

Di folder `backend`, edit `.env`:

```bash
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
```

### Step 4: Whitelist IP (Optional)

1. Di Supabase: **Project Settings** → **Network**
2. Klik **"Add a new IPv4 address"**
3. Gunakan `0.0.0.0/0` untuk development

### Step 5: Test Connection

```bash
cd backend
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit()
```

### Step 6: Run Migrations

```bash
php artisan migrate
```

**Output should show:**

```
Migrating: ... (creates all 15 tables)
Migrated: (success message)
```

### Step 7: Seed Sample Data

```bash
php artisan db:seed
```

**Output should show:**

```
Seeding: Database\Seeders\RoleSeeder
Seeded: Database\Seeders\RoleSeeder (6 rows)
Seeding: Database\Seeders\CompleteDataSeeder
Seeded: Database\Seeders\CompleteDataSeeder (complete data created)
...
```

### Step 8: Verify Database di Supabase

Di Supabase dashboard, buka **SQL Editor** dan run:

```sql
SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'public';
SELECT * FROM roles;
SELECT * FROM users LIMIT 5;
SELECT * FROM students;
EXIT;
```

---

## 📚 Testing Login Credentials

### For Development:

| Email                         | Password    | Role         | Access                   |
| ----------------------------- | ----------- | ------------ | ------------------------ |
| admin@sekolah.com             | password123 | Super Admin  | Full system access       |
| test.guru1@sekolah.com        | password123 | Guru         | Grade & Attendance input |
| test.siswa1@sekolah.com       | password123 | Murid        | Student dashboard        |
| test.staff.kantin@sekolah.com | password123 | Staff Kantin | Cafeteria scanning       |

---

## 🔧 Next Steps to Implement

### 1. API Controllers (HIGH PRIORITY)

```
app/Http/Controllers/
├── AuthController.php
├── StudentController.php
├── DormController.php
├── CafeteriaController.php
├── AttendanceController.php
├── GradeController.php
├── ClassController.php
├── AdminController.php
└── SubjectController.php
```

### 2. Middleware for Authorization

```
app/Http/Middleware/
├── CheckAdmin.php
├── CheckTeacher.php
├── CheckStaffAsrama.php
├── CheckStaffKantin.php
├── CheckStudent.php
└── CheckPermission.php
```

### 3. Request Validation Classes

```
app/Http/Requests/
├── StoreStudentRequest.php
├── StoreGradeRequest.php
├── StoreAttendanceRequest.php
├── SelectDormRoomRequest.php
└── ...
```

### 4. Vue.js Components

```
src/components/
src/views/
├── StudentRegistration.vue
├── DormSelection.vue
├── StudentGrades.vue
├── StudentAttendance.vue
├── CafeteriaMealLog.vue
├── AdminDashboard.vue
└── ...
```

### 5. API Integration Files

```
src/api/
├── auth.js (update)
├── student.js
├── dorm.js
├── cafeteria.js
├── grade.js
├── attendance.js
└── admin.js
```

---

## 🐛 Troubleshooting Supabase

### Error: "SQLSTATE[08006]: could not translate host name"

**Penyebab:** Host/password salah atau IP belum di-whitelist
**Solusi:**

- Cek hostname di .env sudah benar (dari Supabase dashboard)
- Pastikan IP sudah di-whitelist atau gunakan `0.0.0.0/0`

```bash
# Test koneksi di Terminal
psql -h db.xxxxxxxxxxxxx.supabase.co -U postgres -d postgres
# Masukkan password saat diminta
```

### Error: "FATAL: remaining connection slots are reserved"

**Penyebab:** Free tier Supabase punya connection limit
**Solusi:**

- Tutup semua koneksi yang tidak digunakan
- Jika sering terjadi, upgrade ke plan berbayar

### Error: "no pg_hba.conf entry for host"

**Penyebab:** SSL requirement tidak terpenuhi
**Solusi:** Pastikan .env ada:

```env
DB_CONNECTION=pgsql
```

Dan `config/database.php` sudah set `'sslmode' => 'require'`

### Lupa Password Supabase

1. Buka https://app.supabase.com → Project
2. **Project Settings** → **Database** → **Reset password**
3. Update password baru di .env file

### Migration Failed

```bash
# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset

# HATI-HATI: Fresh start (delete all data)
php artisan migrate:fresh --seed
```

### Foreign Key Errors

Di Supabase, foreign keys aktif by default. Jika ada error:

```env
# Temporary workaround
DB_FOREIGN_KEYS=false
```

Kemudian retry migrations. \*\*Jangan lupa return ke `true` setelah selesai!

### Permission Issues

```bash
chmod -R 775 storage bootstrap/cache
```

---

## 📊 Database Structure Diagram

```
users (admin login juga)
    ├── roles (permissions)
    ├── students (dengan registration tracking)
    │   ├── dorm_rooms
    │   │   ├── dorm_buildings
    │   │   └── dorm_types
    │   ├── student_classes
    │   │   └── classes
    │   │       ├── academic_years
    │   │       ├── subjects
    │   │       ├── class_schedules
    │   │       │   ├── class_schedules
    │   │       │   └── subjects
    │   ├── attendances
    │   │   └── class_schedules
    │   ├── grades
    │   │   ├── subjects
    │   │   └── academic_years
    │   └── cafeteria_logs
    │       └── cafeteria_menus
```

---

## 💾 File Changes Summary

### New Files Created

1. `database/migrations/2025_03_19_000000_add_verification_and_role_to_users_table.php`
2. `database/seeders/CompleteDataSeeder.php`
3. `app/Models/Role.php`
4. `app/Models/Student.php`
5. `app/Models/AcademicYear.php`
6. `app/Models/Classes.php`
7. `app/Models/StudentClass.php`
8. `app/Models/ClassSchedule.php`
9. `app/Models/Attendance.php`
10. `app/Models/Grade.php`
11. `app/Models/CafeteriaMenu.php`
12. `app/Models/MealLog.php`
13. `app/Models/DormBuilding.php`
14. `app/Models/DormType.php`
15. `app/Models/DormRoom.php`
16. `DATABASE_SETUP_GUIDE.md`
17. `backend/routes/API_ROUTES_REFERENCE.php`

### Files Modified

1. `database/seeders/DatabaseSeeder.php` - Added CompleteDataSeeder
2. `app/Models/User.php` - Added is_verified, role_id, role relationship

---

## ✅ Verification Commands

```bash
# Check all tables created
php artisan tinker
>>> DB::table('roles')->count()
>>> DB::table('users')->count()
>>> DB::table('students')->count()
>>> exit()

# Test a query
php artisan tinker
>>> App\Models\User::with('role')->first()
>>> App\Models\Student::with('assignedRoom', 'user')->first()
>>> exit()
```

---

## 📞 Support

If you encounter issues:

1. Check `DATABASE_SETUP_GUIDE.md` for detailed instructions
2. Review `API_ROUTES_REFERENCE.php` for endpoint structure
3. Check migrationStatus: `php artisan migrate:status`
4. Review database logs: `storage/logs/laravel-*.log`

---

**Status:** ✅ Database implementation complete and ready for API development

**Last Updated:** 2025-03-19
