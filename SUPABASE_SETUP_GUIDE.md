# 🚀 Supabase Setup Guide untuk Skrippsii

Panduan lengkap setup database menggunakan Supabase (PostgreSQL Cloud) untuk aplikasi Skrippsii.

## 📚 Table of Contents

1. [Apa itu Supabase?](#apa-itu-supabase)
2. [Keuntungan menggunakan Supabase](#keuntungan-menggunakan-supabase)
3. [Step-by-Step Setup](#step-by-step-setup)
4. [Verify Database](#verify-database)
5. [Troubleshooting](#troubleshooting)

---

## Apa itu Supabase?

**Supabase** adalah open-source Firebase alternative yang menyediakan:

- PostgreSQL database terhostess secara cloud
- Real-time subscriptions
- Built-in authentication
- Storage untuk files
- Deployable ke production dengan mudah

**URL:** https://app.supabase.com

---

## Keuntungan menggunakan Supabase

✅ **Database Terhostess** - Tidak perlu install MySQL/PostgreSQL di lokal  
✅ **Akses dari Mana Saja** - Cloud-based, bisa diakses dari mana saja  
✅ **Free Tier** - Cukup untuk development dan hobby projects  
✅ **PostgreSQL** - Lebih powerful daripada MySQL  
✅ **Easy to Scale** - Upgrade plan kapan saja tanpa migrasi  
✅ **Built-in Tools** - SQL editor, backup, monitoring  
✅ **Production-Ready** - Siap untuk deployment

---

## Step-by-Step Setup

### Step 1: Create Supabase Account

1. Buka https://app.supabase.com
2. Klik **"Sign Up"** atau **"Create a new project"**
3. Pilih method login:
   - GitHub (recommended)
   - Google
   - Email

### Step 2: Create Project

1. Setelah login, klik **"New Project"**
2. Isi form:

   ```
   Project Name:        skrippsii
   Database Password:   XYZpassw0rd!@#  (⚠️ SAVE THIS!)
   Region:             Singapore, Tokyo, atau Jakarta (pilih terdekat)
   ```

3. Klik **"Create new project"**
4. Tunggu ~2 menit sampai project ready

### Step 3: Get Connection Credentials

1. Setelah project siap, buka **"Project Settings"**
2. Klik tab **"Database"**
3. Di bagian **"Connection string"** atau **"Connection info"**, cari:

   ```
   Host:       db.xxxxxxxxxxxxx.supabase.co
   Port:       5432
   Database:   postgres
   Username:   postgres
   Password:   XYZpassw0rd!@#  (password dari Step 2)
   ```

**COPY-PASTE ke tempat aman!** Kamu akan butuh info ini untuk `.env` file.

### Step 4: Configure Laravel .env

1. Di folder `backend/`, buka file `.env`
2. Cari atau buat section database:

```env
# Old (delete atau comment):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# New (Supabase):
DB_CONNECTION=pgsql
DB_HOST=db.abcdefghijklmno.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=XYZpassw0rd!@#
```

**Contoh .env lengkap:**

```env
APP_NAME=Skrippsii
APP_ENV=local
APP_KEY=base64:PWNBQGY+jwc8FEOcPTuMv6PgjGQYoai9ga6IO/ioECg=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=db.abcdefghijklmno.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Pa$$w0rd123!@#

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Step 5: Whitelist Your IP (Strongly Recommended)

Supabase memiliki firewall. IP kamu harus di-whitelist untuk connect:

1. Di Supabase dashboard, buka **"Project Settings"**
2. Klik tab **"Network"**
3. Di bagian **"IP Whitelist"**, klik **"Add a new IPv4 address"**
4. Masukkan IP:
   - **Untuk Development:** `0.0.0.0/0` (allow semua IP)
   - **Untuk Production:** masukkan IP spesifik server kamu

5. Klik **"Add"**

⚠️ **Untuk production, jangan gunakan `0.0.0.0/0`!**

### Step 6: Install PostgreSQL Driver (di Local)

Jika belum install, install PHP PostgreSQL driver:

```bash
# MacOS (Homebrew)
brew install php-pgsql

# Linux (Ubuntu/Debian)
sudo apt-get install php-pgsql

# Windows
# Biasanya sudah included dalam installer PHP, cek di php.ini
```

### Step 7: Test Connection

```bash
cd backend

# Test koneksi Laravel
php artisan tinker
>>> DB::connection()->getPdo()
# Jika berhasil: PDO object terlihat
# Jika error: cek .env credentials & IP whitelist

>>> exit()
```

Atau test langsung via terminal:

```bash
# Install psql client (PostgreSQL)
psql -h db.abcdefghijklmno.supabase.co \
     -U postgres \
     -d postgres

# Masukkan password saat diminta
# Jika berhasil: psql> prompt terlihat
```

### Step 8: Run Migrations

```bash
cd backend
php artisan migrate
```

Output:

```
Migrating: 2025_02_26_100001_create_roles_table
Migrating: 2025_02_26_100003_create_dorm_tables
Migrating: 2025_02_26_100004_create_students_table
... (dan seterusnya)

Migrated: (15 migrations total)
```

### Step 9: Seed Sample Data

```bash
php artisan db:seed
```

Output:

```
Seeding: Database\Seeders\RoleSeeder
Seeded: Database\Seeders\RoleSeeder (6 roles added)

Seeding: Database\Seeders\CompleteDataSeeder
Seeded: Database\Seeders\CompleteDataSeeder (users, dorms, students added)

... (dan seterusnya)
```

### Step 10: Verify Database di Supabase UI

1. Buka https://app.supabase.com → Project kamu
2. Buka **"SQL Editor"** di sidebar kiri
3. Jalankan query untuk verify:

```sql
-- Check semua tabel
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;

-- Check data di roles
SELECT * FROM roles;

-- Check data di users
SELECT id, name, email, role FROM users LIMIT 10;

-- Check total students
SELECT COUNT(*) as total_students FROM students;
```

---

## Troubleshooting

### Error: "SQLSTATE[08006]: could not translate host name"

**Penyebab:**

- Hostname di .env salah
- Network connection issue
- IP belum di-whitelist

**Solusi:**

```bash
# 1. Cek hostname di Supabase
# Buka: Project Settings → Database → Connection info

# 2. Cek .env sudah benar
cat .env | grep DB_

# 3. Test koneksi manual
psql -h db.abcdefgh.supabase.co -U postgres -d postgres
# Enter password ketika diminta

# 4. Jika masih error, check IP whitelist di Supabase
```

### Error: "FATAL: password authentication failed"

**Penyebab:** Password salah atau sudah expire

**Solusi:**

```bash
# 1. Reset password di Supabase
# Project Settings → Database → "Reset password"

# 2. Copy password baru
# 3. Update .env dengan password baru
# 4. Retry connection
```

### Error: "FATAL: remaining connection slots are reserved for superuser"

**Penyebab:** Free tier memiliki connection limit (~20 connections)

**Solusi:**

- Tutup semua connection yang tidak digunakan
- Check di Supabase dashboard berapa connection aktif
- Jika sering terjadi, upgrade plan

```bash
# Di Supabase SQL Editor, check active connections:
SELECT count(*) FROM pg_stat_activity;
```

### Error: "no pg_hba.conf entry for host"

**Penyebab:** Connection string atau IP config salah

**Solusi:**

- Pastikan sslmode di database.php set ke 'require'
- Supabase memerlukan SSL connection

```php
// config/database.php
'pgsql' => [
    ...
    'sslmode' => 'require',  // ✅ Harus ada ini
],
```

### Error: "ERROR: syntax error" saat migrate

**Penyebab:** PostgreSQL syntax berbeda dari MySQL

**Solusi:**

- Migrations sudah di-optimize untuk PostgreSQL
- Jika ada error, report ke developer

```bash
# Check migration status
php artisan migrate:status

# Jika perlu rollback
php artisan migrate:rollback
```

### Lupa Password Database

**Solusi:**

1. Buka https://app.supabase.com → Project
2. **Project Settings** → **Database**
3. Scroll ke bawah → **"Reset password"**
4. Masukkan password baru
5. Copy password baru
6. Update `.env` dengan password baru:

```env
DB_PASSWORD=new_password_here
```

7. Test koneksi:

```bash
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit()
```

---

## Database Backup & Export

### Automatic Backup

Supabase automatically backup database kamu:

- Daily backups (free tier: 7 hari retention)
- Weekly backups (premium plans)

Lihat di: **Project Settings** → **Backups**

### Manual Export

Export ke SQL file:

```bash
# Via Terminal
pg_dump -h db.abcdefgh.supabase.co \
        -U postgres \
        -d postgres \
        > backup.sql

# Via Supabase UI
# SQL Editor → download hasil query
```

---

## Production Deployment

Ketika siap deploy ke production:

1. **Upgrade Supabase Plan**
   - Free tier: 500MB storage, 2GB transfer/month
   - Pro tier: Unlimited storage, hourly backups, 24/7 support

2. **Security Best Practices**

   ```env
   # JANGAN pernah push credentials ke Git!
   # Gunakan .env.example (tanpa passwords)
   # Di server: set env variables via environment/secrets
   ```

3. **Enable SSL in Production**

   ```php
   'sslmode' => 'require',  // Always require SSL in prod
   ```

4. **Setup Monitoring**
   - Buka Supabase dashboard regularly
   - Check backups status
   - Monitor storage usage

---

## Useful Supabase Resources

- **Documentation:** https://supabase.com/docs
- **API Reference:** https://supabase.com/docs/reference/api
- **Community Forum:** https://github.com/supabase/supabase/discussions
- **Status Page:** https://status.supabase.com

---

## Cheat Sheet

```bash
# Test koneksi
psql -h db.xxx.supabase.co -U postgres -d postgres

# Laravel - check connection
php artisan tinker
>>> DB::connection()->getPdo()
>>> exit()

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed

# Rollback last migration
php artisan migrate:rollback

# Check logs
tail -f storage/logs/laravel-*.log

# Reset everything (⚠️ Destructive)
php artisan migrate:fresh --seed
```

---

**Last Updated:** 2025-03-19  
**Framework:** Laravel 11 + PostgreSQL + Supabase  
**Status:** ✅ Ready for Development & Production
