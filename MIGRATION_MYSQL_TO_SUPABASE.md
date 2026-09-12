# 🔄 Database Migration: MySQL → Supabase (PostgreSQL)

## Summary of Changes

Database telah dimigrasikan dari **MySQL lokal** ke **Supabase PostgreSQL Cloud**.

---

## 📊 Perbandingan

| Aspek             | MySQL Lokal            | Supabase PostgreSQL     |
| ----------------- | ---------------------- | ----------------------- |
| **Hosting**       | Lokal / Server         | Cloud (terhostess)      |
| **Database Type** | MySQL                  | PostgreSQL              |
| **Setup**         | Manual install         | Auto setup              |
| **Akses**         | Lokal/LAN              | Global internet         |
| **Backup**        | Manual                 | Otomatis harian         |
| **Cost**          | Free (lokal)           | Free tier atau berbayar |
| **Performa**      | Tergantung setup lokal | Optimized cloud         |
| **Maintenance**   | Manual                 | Managed by Supabase     |

---

## ✅ Files Modified

### 1. **backend/.env**

```diff
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=laravel
- DB_USERNAME=root
- DB_PASSWORD=

+ DB_CONNECTION=pgsql
+ DB_HOST=db.xxxxxxxxxxxxx.supabase.co
+ DB_PORT=5432
+ DB_DATABASE=postgres
+ DB_USERNAME=postgres
+ DB_PASSWORD=your_supabase_password
```

### 2. **backend/config/database.php**

```php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'search_path' => 'public',
    'sslmode' => 'require',  // 👈 IMPORTANT: Supabase memerlukan SSL
],
```

### 3. **backend/database/migrations/2025_03_19_000000_add_verification_and_role_to_users_table.php**

✅ No changes needed (kompatibel dengan PostgreSQL)

### 4. **backend/database/seeders/CompleteDataSeeder.php**

✅ No changes needed (SQL syntax kompatibel)

---

## 📁 Files Created

### 1. **SUPABASE_SETUP_GUIDE.md** (NEW)

Panduan lengkap step-by-step untuk setup Supabase dengan screenshots & troubleshooting.

### 2. **backend/.env.example.supabase** (NEW)

Template .env untuk Supabase dengan format yang benar.

---

## 📋 Configuration Checklist

- [x] Update DB_CONNECTION dari mysql ke pgsql
- [x] Update DB_HOST ke Supabase host
- [x] Update DB_DATABASE ke postgres
- [x] Update DB_USERNAME ke postgres
- [x] Set DB_PASSWORD ke Supabase password
- [x] Set SSL mode to 'require' di config/database.php
- [x] Update DATABASE_SETUP_GUIDE.md
- [x] Update SETUP_CHECKLIST.md
- [x] Create SUPABASE_SETUP_GUIDE.md

---

## 🚀 How to Setup

### Metode Cepat (5 Menit)

1. **Create Supabase Project**

   ```
   https://app.supabase.com → New Project
   Project name: skrippsii
   Region: Singapore/Japan/Jakarta
   Save password ✅
   ```

2. **Get Credentials**

   ```
   Project Settings → Database → Connection info
   Copy: Host, Username, Password
   ```

3. **Update .env**

   ```env
   DB_HOST=db.xxxxx.supabase.co
   DB_PASSWORD=your_password
   ```

4. **Whitelist IP** (optional tapi recommended)

   ```
   Supabase → Project Settings → Network → Add IPv4
   Use 0.0.0.0/0 untuk development
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

### Metode Lengkap

Baca: [SUPABASE_SETUP_GUIDE.md](../SUPABASE_SETUP_GUIDE.md)

---

## 🔧 Troubleshooting Quick Links

| Error                            | Solution                                 |
| -------------------------------- | ---------------------------------------- |
| "could not translate host name"  | Cek hostname di Supabase, IP whitelist   |
| "password authentication failed" | Reset password di Supabase settings      |
| "connection slots reserved"      | Free tier connection limit, upgrade plan |
| "no pg_hba.conf entry for host"  | Pastikan sslmode = 'require' di config   |

Detail: Lihat SUPABASE_SETUP_GUIDE.md → Troubleshooting

---

## 📈 Benefits of Supabase

✅ **No Local Setup** - Database ready in 2 minutes  
✅ **Always Available** - Cloud infrastructure  
✅ **Automatic Backups** - Daily backups included  
✅ **Easy to Scale** - Upgrade or downgrade anytime  
✅ **Production Ready** - Deploy kapan saja  
✅ **Built-in Tools** - SQL editor, monitoring, etc  
✅ **PostgreSQL Power** - More features than MySQL

---

## 🔐 Security Considerations

1. **Never commit .env to Git**

   ```bash
   # Check .gitignore
   cat .gitignore | grep .env
   ```

2. **Use Strong Password**
   - Min 12 characters
   - Mix: uppercase, lowercase, numbers, symbols

3. **Whitelist IPs**
   - For production: specific IPs only
   - For development: `0.0.0.0/0` is OK

4. **Enable 2FA**
   - Supabase Account Settings → 2FA

5. **Regular Backups**
   - Check backups di Supabase → Backups
   - Enable automated backups (paid plans)

---

## 📚 Documentation References

| File                        | Purpose                              |
| --------------------------- | ------------------------------------ |
| **SUPABASE_SETUP_GUIDE.md** | Detailed step-by-step Supabase setup |
| **SETUP_CHECKLIST.md**      | Quick start summary (updated)        |
| **DATABASE_SETUP_GUIDE.md** | Database structure & API (updated)   |
| **.env.example.supabase**   | Env template for Supabase            |

---

## 📞 Quick Reference

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo()

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed

# Reset everything
php artisan migrate:fresh --seed  # ⚠️ Deletes all data!

# Check logs
tail -f storage/logs/laravel-*.log
```

---

## ✨ Next Steps

1. ✅ Database migration to Supabase complete
2. ⬜ Create API controllers
3. ⬜ Implement authorization middleware
4. ⬜ Build Vue.js components
5. ⬜ Test end-to-end

---

**Status:** ✅ Ready to use  
**Last Updated:** 2025-03-19  
**Framework:** Laravel 11 + PostgreSQL + Supabase
