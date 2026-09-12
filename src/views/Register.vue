<template>
  <div class="register-page">
    <div class="register-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="register-logo" />
      <h1 class="register-title">Buat Akun</h1>
      <p class="register-subtitle">Daftar untuk mengakses SLAPUR System</p>

      <form class="register-form" @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="nama">Nama Lengkap</label>
          <input
            id="nama"
            v-model="form.nama"
            type="text"
            placeholder="Masukkan nama lengkap"
            required
          />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Masukkan email"
            required
          />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Min. 6 karakter"
            required
            minlength="6"
          />
        </div>
        <div class="form-group">
          <label for="confirm">Konfirmasi Password</label>
          <input
            id="confirm"
            v-model="form.confirmPassword"
            type="password"
            placeholder="Ulangi password"
            required
          />
        </div>
        <div class="form-group">
          <label for="jenis">Jenis Kelamin</label>
          <select id="jenis" v-model="form.jenisKelamin" required>
            <option value="laki-laki">Siswa Laki-laki</option>
            <option value="perempuan">Siswa Perempuan</option>
          </select>
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        <button type="submit" class="register-btn" :disabled="loading">
          <span v-if="loading" class="btn-loading"
            ><span class="spinner"></span> Menyimpan ke database...</span
          >
          <span v-else>Daftar</span>
        </button>
      </form>
      <p class="register-footer">
        Sudah punya akun?
        <router-link to="/siswa" class="link">Login di sini</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { authApi } from "@/api/auth";

const STORAGE_USERS = "slapur_users";

const router = useRouter();
const form = reactive({
  nama: "",
  email: "",
  password: "",
  confirmPassword: "",
  jenisKelamin: "laki-laki",
});
const loading = ref(false);
const errorMsg = ref("");
const successMsg = ref("");

async function handleRegister() {
  errorMsg.value = "";
  successMsg.value = "";
  if (form.password !== form.confirmPassword) {
    errorMsg.value = "Konfirmasi password tidak sama.";
    return;
  }
  if (form.password.length < 6) {
    errorMsg.value = "Password minimal 6 karakter.";
    return;
  }
  loading.value = true;
  try {
    const res = await authApi.register(form);
    if (!res || !res.token) {
      errorMsg.value =
        "Terjadi kesalahan: respons server tidak valid. Coba lagi.";
      return;
    }
    authApi.setToken(res.token);
    if (res.user && typeof res.user === "object") {
      localStorage.setItem("user", JSON.stringify(res.user));
    }
    successMsg.value =
      res.message ||
      "Akun berhasil dibuat dan tersimpan di database. Mengalihkan ke dashboard...";
    const jk =
      res.user?.jenis_kelamin === "perempuan" ? "perempuan" : "laki-laki";
    setTimeout(() => router.push(`/siswa/${jk}/dashboard`), 1200);
  } catch (e) {
    const msg = e?.message || "Pendaftaran gagal.";
    if (e?.errors?.email) {
      errorMsg.value = Array.isArray(e.errors.email)
        ? e.errors.email[0]
        : e.errors.email;
    } else if (e?.errors && typeof e.errors === "object") {
      const first = Object.values(e.errors).flat().find(Boolean);
      errorMsg.value = first || msg;
    } else if (msg.includes("sudah terdaftar") || msg.includes("unique")) {
      errorMsg.value =
        "Email ini sudah terdaftar. Silakan login dengan email ini.";
    } else if (
      msg.includes("Koneksi gagal") ||
      msg.includes("Failed to fetch") ||
      msg.includes("NetworkError")
    ) {
      errorMsg.value =
        "Koneksi gagal. Pastikan backend Laravel berjalan (php artisan serve di folder backend).";
    } else if (msg.includes("Terjadi kesalahan") && e?.status) {
      errorMsg.value = `Terjadi kesalahan (${e.status}). Pastikan backend berjalan di http://127.0.0.1:8000 dan migrasi sudah dijalankan (php artisan migrate).`;
    } else {
      errorMsg.value = msg;
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 50%, #f5f3ff 100%);
  padding: 1rem;
}
.register-card {
  width: 100%;
  max-width: 400px;
  padding: 2.5rem;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  text-align: center;
}
.register-logo {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.25rem;
  display: block;
}
.register-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}
.register-subtitle {
  font-size: 0.95rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}
.register-form {
  text-align: left;
}
.form-group {
  margin-bottom: 1.25rem;
}
.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.4rem;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #0f766e;
  box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
}
.form-group input::placeholder {
  color: #9ca3af;
}
.form-group select {
  cursor: pointer;
  background: #fff;
}
.error-msg {
  font-size: 0.875rem;
  color: #dc2626;
  margin-bottom: 1rem;
}
.success-msg {
  font-size: 0.875rem;
  color: #0f766e;
  margin-bottom: 1rem;
}
.register-btn {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  background: #0f766e;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.register-btn:hover:not(:disabled) {
  background: #0d9488;
}
.register-btn:disabled {
  opacity: 0.85;
  cursor: wait;
}
.btn-loading {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.register-footer {
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: #64748b;
}
.register-footer .link,
.register-footer .link:visited,
.register-footer .link:active,
.register-footer .link:focus {
  color: #0f766e !important;
  font-weight: 600;
  text-decoration: none;
  background: transparent;
}
.register-footer .link:hover {
  text-decoration: underline;
  color: #0c6b63 !important;
}
</style>
