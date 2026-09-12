# Penyimpanan Akun (Database)

## Satu email = satu akun, tersimpan permanen

- **Buat akun** disimpan di **database server** (file `storage/app/database.sqlite`), bukan hanya di browser.
- Satu alamat email hanya bisa didaftarkan **sekali**. Jika sudah terdaftar, akan muncul: **"Email ini sudah terdaftar. Silakan login dengan email ini."**
- Setelah daftar, Anda **tidak perlu buat akun lagi**; cukup **login** dengan email dan password yang sama.

## Mengapa dulu akun bisa hilang?

Sebelumnya akun hanya disimpan di **localStorage** browser. Data bisa hilang jika:
- cache/cookie dibersihkan,
- ganti browser atau perangkat,
- atau pindah device.

Sekarang akun disimpan di **database di server** (folder `backend/storage/app/database.sqlite`), sehingga data tetap ada selama file database tidak dihapus.

## Menjalankan backend

Agar buat akun dan login terhubung ke database:

```bash
cd backend
php artisan serve
```

Lalu jalankan frontend (`npm run dev`) dan buka http://localhost:5173.

## Jika sudah pernah daftar tapi "hilang"

- **Satu kali lagi**: buat akun dengan **email yang sama** dan password baru. Setelah tersimpan di server, gunakan itu untuk login ke depan.
- Jika muncul **"Email ini sudah terdaftar"**: artinya email itu sudah ada di database. Gunakan **Login** dengan password yang dulu Anda pakai (atau gunakan fitur lupa password jika sudah disediakan).
