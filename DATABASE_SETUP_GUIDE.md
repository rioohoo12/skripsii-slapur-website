# Database Setup & Implementation Guide

Panduan lengkap untuk mengatur database, menjalankan migrations, dan seeders sesuai dengan spesifikasi tabel yang telah diberikan.

## Struktur Database

Database mengikuti struktur berikut:

### 1. **Roles** (Hak Akses dengan Boolean)

- Mendefinisikan role pengguna: Super Admin, Guru, Murid, Staff Asrama, Staff Kantin, Administrasi
- Setiap role memiliki permission boolean untuk berbagai fitur

### 2. **Users** (Login Satu Pintu)

- Menyimpan data login semua pengguna
- Terkoneksi ke `roles` table untuk permission management
- Fields: email, password (hashed), full_name, role_id, is_verified

### 3. **Students** (Murid/Siswa)

- Menyimpan data siswa dengan tracking status pendaftaran
- Fields untuk status: has_paid, has_uploaded_docs, has_selected_dorm_type, room_id, has_taken_key
- Support untuk siswa baru dan pindahan

### 4. **Rooms/Dorm** (Asrama)

- DormTypes: Tipe asrama (Putra/Putri)
- DormBuildings: Gedung asrama
- DormRooms: Ruangan individual dengan kapasitas

### 5. **Academic** (Akademik)

- AcademicYears: Tahun akademik dan semester
- Classes: Kelas pelajaran
- Subjects: Mata pelajaran
- ClassSchedules: Jadwal pelajaran (hari, jam, guru, matpel)
- StudentClasses: Hubungan siswa ke kelas (many-to-many)

### 6. **Attendance** (Absensi)

- Mencatat kehadiran siswa per jadwal pelajaran
- Status: Hadir, Izin, Sakit, Alpa

### 7. **Grades** (Nilai)

- Menyimpan nilai siswa per mata pelajaran
- Support untuk berbagai tipe penilaian: Tugas, UH, UTS, UAS

### 8. **Cafeteria** (Kafetaria)

- CafeteriaMenus: Menu makanan terjadwal (Pagi, Siang, Sore)
- MealLogs: Log pemindaian kartu makan siswa

---

## Setup Instructions

### 1. Configure Database Connection (Supabase)

#### Step 1a: Create Supabase Project

1. Buka https://app.supabase.com
2. Klik "New Project"
3. Isi project details:
   - Project name: `skrippsii`
   - Database password: (simpan password ini aman!)
   - Region: Indonesia (jika tersedia) atau Asia terdekat
4. Tunggu project selesai dibuat (~2 menit)

#### Step 1b: Get Connection String

1. Di Supabase dashboard, buka **Project Settings** → **Database**
2. Cari bagian **Connection string** atau **Connection info**
3. Copy credentials berikut:
   - **Host**: Dari connection string atau field "Host"
   - **Port**: `5432` (default)
   - **Database**: `postgres`
   - **Username**: `postgres`
   - **Password**: Password yang kamu set saat create project

#### Step 1c: Update .env

Edit file `backend/.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password_here

# Contoh lengkap:
DB_CONNECTION=pgsql
DB_HOST=db.abcdefghijklmnop.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Pa$$w0rd123!@#
```

### 2. Test Database Connection

```bash
cd backend

# Test connection
php artisan tinker
>>> DB::connection()->getPdo()
// Jika koneksi berhasil, akan menampilkan PDO object

>>> exit()
```

Jika ada error "SQLSTATE[08006]", kemungkinan:

- Host/password salah
- IP belum di-whitelist (lihat Step berikutnya)

#### (Optional) Whitelist IP di Supabase

1. Buka Supabase dashboard → **Project Settings** → **Network**
2. Klik **Add a new IPv4 address**
3. Masukkan IP address mu atau gunakan `0.0.0.0/0` untuk development

### 3. Run Migrations

```bash
cd backend
php artisan migrate
```

Migrations akan membuat semua tabel sesuai spesifikasi.

### 4. Run Seeders

Untuk mengisi database dengan data sample:

```bash
php artisan db:seed
```

Atau seeder tertentu:

```bash
# Seed hanya roles
php artisan db:seed --class=RoleSeeder

# Seed lengkap dengan data sample
php artisan db:seed --class=CompleteDataSeeder
```

### 5. Fresh Migration (Reset Database - Hati-hati!)

Untuk reset **seluruh** database (hapus semua data) dan jalankan semua migrations + seeders:

```bash
cd backend
php artisan migrate:fresh --seed
```

⚠️ **WARNING:** Perintah ini akan menghapus SEMUA tabel dan data di Supabase. Hanya gunakan di development!

---

## Troubleshooting Supabase

### Error: "SQLSTATE[08006]: could not translate host name"

**Solusi:**

- Cek hostname di .env sudah benar
- Pastikan IP sudah di-whitelist di Supabase settings

### Error: "FATAL: remaining connection slots are reserved"

**Solusi:**

- Supabase free tier punya connection limit
- Shutdown unnecessary local connections atau upgrade plan

### Reset Password Supabase

1. Buka https://app.supabase.com
2. Project Settings → Database → Reset password
3. Update password di .env
4. Restart Laravel

---

## Sample Data Included

### Users untuk Testing

| Email                         | Password    | Role         | Notes                  |
| ----------------------------- | ----------- | ------------ | ---------------------- |
| admin@sekolah.com             | password123 | Super Admin  | Full access            |
| test.guru1@sekolah.com        | password123 | Guru         | Pengajar               |
| test.guru2@sekolah.com        | password123 | Guru         | Pengajar               |
| test.guru3@sekolah.com        | password123 | Guru         | Pengajar               |
| test.staff.asrama@sekolah.com | password123 | Staff Asrama | Manage asrama          |
| test.staff.kantin@sekolah.com | password123 | Staff Kantin | Manage kantin          |
| test.administrasi@sekolah.com | password123 | Administrasi | Verify payments & docs |
| test.siswa1@sekolah.com       | password123 | Murid        | Siswa 1                |
| test.siswa2@sekolah.com       | password123 | Murid        | Siswa 2                |
| test.siswa3@sekolah.com       | password123 | Murid        | Siswa 3                |
| test.siswa4@sekolah.com       | password123 | Murid        | Siswa 4                |
| test.siswa5@sekolah.com       | password123 | Murid        | Siswa 5                |

### Sample Data Created

- **Roles**: 6 roles dengan permission berbeda
- **Dorm Buildings**: 2 gedung (Asrama Putri A, Asrama Putra A)
- **Rooms**: 20 kamar (10 putri, 10 putra) dengan kapasitas 4 per kamar
- **Academic Year**: 2024/2025 Semester Ganjil
- **Subjects**: 10 mata pelajaran
- **Classes**: 3 kelas (X-IPA-1, X-IPA-2, X-IPS-1)
- **Students**: 5 siswa dengan berbagai status registrasi
- **Cafeteria Menus**: 3 menu (Pagi, Siang, Sore) untuk hari ini
- **Sample Attendance, Grades, Meal Logs**: Data random untuk testing

---

## API Integration

### Key Models Created

Berikut model Laravel yang telah dibuat untuk front-end integration:

```
App\Models\
├── User.php (updated)
├── Role.php (new)
├── Student.php (new)
├── AcademicYear.php (new)
├── Classes.php (new)
├── ClassSchedule.php (new)
├── StudentClass.php (new)
├── Attendance.php (new)
├── Grade.php (new)
├── Subject.php (existing)
├── DormBuilding.php (new)
├── DormType.php (new)
├── DormRoom.php (new)
├── CafeteriaMenu.php (new)
├── MealLog.php (new)
└── ...
```

### Example API Routes (To be Created)

```php
// Routes/api.php

// Auth Routes
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/register

// Student Routes
GET    /api/students                    // Get all students
GET    /api/students/{id}               // Get student detail
GET    /api/students/{id}/registration-progress
GET    /api/students/{id}/grades
GET    /api/students/{id}/attendance
GET    /api/students/{id}/meal-logs

// Dorm Routes
GET    /api/dorms/buildings
GET    /api/dorms/rooms
GET    /api/dorms/rooms/{id}/available

// Cafeteria Routes
GET    /api/cafeteria/menus
POST   /api/cafeteria/meals/scan       // Scan meal card

// Attendance Routes
POST   /api/attendance                  // Record attendance
GET    /api/attendance/student/{id}

// Grade Routes
GET    /api/grades/student/{id}
POST   /api/grades                      // Input grade
PUT    /api/grades/{id}

// Admin Routes
GET    /api/admin/roles
POST   /api/admin/users
PUT    /api/admin/users/{id}
```

---

## Frontend Integration

### Vue.js Components to Create/Update

1. **StudentRegistration.vue** - Track: pembayaran → upload dokumen → pilih asrama → pilih kamar → ambil kunci
2. **DormSelection.vue** - Display available rooms dengan kapasitas
3. **StudentDashboard.vue** - Show grades, attendance, meal logs
4. **AdminDashboard.vue** - User management, payment verification, document verification

### API Calls Examples

```javascript
// src/api/student.js
import axios from "axios";

const API_URL = process.env.VUE_APP_API_URL || "http://localhost:8000/api";

export const getStudentData = (studentId) => {
  return axios.get(`${API_URL}/students/${studentId}`);
};

export const getRegistrationProgress = (studentId) => {
  return axios.get(`${API_URL}/students/${studentId}/registration-progress`);
};

export const selectDormRoom = (studentId, roomId) => {
  return axios.post(`${API_URL}/students/${studentId}/select-room`, {
    room_id: roomId,
  });
};

export const submitDocuments = (studentId, formData) => {
  return axios.post(
    `${API_URL}/students/${studentId}/upload-documents`,
    formData,
  );
};
```

---

## Troubleshooting

### Migration Errors

Jika ada error saat `php artisan migrate`:

```bash
# Check migration status
php artisan migrate:status

# Rollback semua migrations
php artisan migrate:reset

# Fresh start (caution: deletes all data)
php artisan migrate:fresh
```

### Seeder Errors

Jika foreign key errors saat seeder:

```env
# Set to false temporarily
DB_FOREIGN_KEYS=false
```

Kemudian jalankan seeders kembali.

### Permission Issues

Jika file permissions error:

```bash
cd backend
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Next Steps

1. ✅ Database migrations created
2. ✅ Models with relationships created
3. ✅ Seeders with sample data created
4. ⬜ Create API controllers and routes
5. ⬜ Integrate with Vue.js frontend
6. ⬜ Add validation rules
7. ⬜ Add authorization policies

---

## File Changes Summary

### Created Files

- `database/migrations/2025_03_19_000000_add_verification_and_role_to_users_table.php`
- `database/seeders/CompleteDataSeeder.php`
- `app/Models/Role.php`
- `app/Models/Student.php`
- `app/Models/AcademicYear.php`
- `app/Models/Classes.php`
- `app/Models/StudentClass.php`
- `app/Models/ClassSchedule.php`
- `app/Models/Attendance.php`
- `app/Models/Grade.php`
- `app/Models/CafeteriaMenu.php`
- `app/Models/MealLog.php`
- `app/Models/DormBuilding.php`
- `app/Models/DormType.php`
- `app/Models/DormRoom.php`

### Updated Files

- `database/seeders/DatabaseSeeder.php` - Added CompleteDataSeeder
- `app/Models/User.php` - Added is_verified, role_id, role relationship
