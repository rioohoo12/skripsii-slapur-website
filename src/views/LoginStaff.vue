<template>
  <div class="login-page login-staff">
    <div class="login-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Login Staff</h1>
      <p class="login-subtitle">SLAPUR System — masuk dengan akun staff</p>

      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Masukkan email"
            required
            autocomplete="username"
          />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Masukkan password"
            required
            autocomplete="current-password"
          />
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <p v-if="loading && loadingLong" class="loading-hint">
          Pastikan backend Laravel (<code>php artisan serve</code>) sedang berjalan.
        </p>
        <button type="submit" class="login-btn staff" :disabled="loading">
          <span v-if="loading" class="btn-loading">
            <span class="spinner"></span>
            {{ loadingLong ? 'Masih memproses...' : 'Memproses...' }}
          </span>
          <span v-else>Login</span>
        </button>
      </form>
      <p class="login-footer">
        <router-link to="/siswa" class="link">Login Siswa</router-link>
        <span class="sep"> · </span>
        <router-link to="/guru" class="link">Login Guru</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { authApi } from '@/api/auth';

const router = useRouter();
const form = reactive({ email: '', password: '' });
const loading = ref(false);
const loadingLong = ref(false);
const errorMsg = ref('');

let loadingLongTimer = null;
watch(loading, (isLoading) => {
  if (!isLoading) {
    loadingLong.value = false;
    if (loadingLongTimer) clearTimeout(loadingLongTimer);
    loadingLongTimer = null;
    return;
  }
  loadingLongTimer = setTimeout(() => { loadingLong.value = true; }, 2500);
});

onMounted(() => {
  try {
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('user');
    if (token && user) {
      const u = JSON.parse(user);
      if (u.role && ['staff', 'admin', 'super_admin'].includes(u.role)) {
        router.replace('/staff/dashboard');
      }
    }
  } catch (_) {}
});

async function handleLogin() {
  errorMsg.value = '';
  loading.value = true;
  try {
    const res = await authApi.loginStaff(form.email.trim(), form.password);
    authApi.setToken(res.token);
    if (res.user) localStorage.setItem('user', JSON.stringify(res.user));
    router.push('/staff/dashboard');
  } catch (e) {
    const msg = e?.message || '';
    if (e?.errors?.email) {
      errorMsg.value = Array.isArray(e.errors.email) ? e.errors.email[0] : e.errors.email;
    } else {
      errorMsg.value = msg || 'Email atau password salah.';
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-staff .login-title { color: #0d9488; }
.login-staff .login-btn.staff {
  background: #0d9488;
}
.login-staff .login-btn.staff:hover:not(:disabled) {
  background: #14b8a6;
}
.login-staff .login-footer .link { color: #0d9488; }
.login-staff .form-group input:focus { border-color: #14b8a6; box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1); }
</style>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 50%, #cffafe 100%);
  padding: 1rem;
}
.login-card {
  width: 100%;
  max-width: 400px;
  padding: 2.5rem;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  text-align: center;
}
.login-logo { width: 80px; height: 80px; margin: 0 auto 1.25rem; display: block; }
.login-subtitle { font-size: 0.95rem; color: #64748b; margin-bottom: 1.5rem; }
.login-form { text-align: left; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  font-family: inherit;
}
.error-msg { font-size: 0.875rem; color: #dc2626; margin-bottom: 1rem; }
.loading-hint { font-size: 0.8rem; color: #64748b; margin: -0.5rem 0 1rem 0; }
.loading-hint code { background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px; }
.login-btn {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}
.login-btn:disabled { opacity: 0.85; cursor: wait; }
.btn-loading { display: inline-flex; align-items: center; gap: 0.5rem; }
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.login-footer { margin-top: 1.5rem; font-size: 0.9rem; color: #64748b; }
.login-footer .link { font-weight: 600; text-decoration: none; }
.login-footer .link:hover { text-decoration: underline; }
</style>
