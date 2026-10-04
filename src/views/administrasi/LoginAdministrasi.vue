<template>
  <div class="login-page login-administrasi">
    <div class="login-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Login Administrasi</h1>
      <p class="login-subtitle">SLAPUR System — masuk dengan akun staff administrasi</p>

      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="administrasi@gmail.com"
            required
            readonly
            autocomplete="username"
            class="locked-input"
          />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Masukkan password"
            required
            readonly
            autocomplete="current-password"
            class="locked-input"
          />
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        
        <button type="submit" class="login-btn staff" :disabled="loading">
          <span v-if="loading" class="btn-loading">
            <span class="spinner"></span>
            Memproses...
          </span>
          <span v-else>Login</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { authApi } from '@/api/auth';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('administrasi@gmail.com');
const password = ref('Administrasi1');
const loading = ref(false);
const errorMsg = ref('');

const handleLogin = async () => {
  errorMsg.value = '';
  if (email.value !== 'administrasi@gmail.com' || password.value !== 'Administrasi1') {
    errorMsg.value = 'Hanya akun khusus Administrasi yang diizinkan.';
    return;
  }

  loading.value = true;
  
  try {
    const res = await authApi.loginStaff(email.value.trim(), password.value);
    
    // Check if the role is a valid staff/administrasi role
    if (!['staff', 'admin', 'super_admin'].includes(res.user.role)) {
       throw new Error('Akses ditolak. Anda bukan Staff Administrasi.');
    }
    
    authStore.setAuth(res.token, res.user);
    router.push('/administrasi/dashboard');
  } catch (err) {
    if (err?.errors?.email) {
      errorMsg.value = Array.isArray(err.errors.email) ? err.errors.email[0] : err.errors.email;
    } else {
      errorMsg.value = err?.message || 'Email atau password salah. Silakan coba lagi.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Theme khusus untuk Administrasi (Biru Laut / Indigo) */
.login-administrasi .login-title { color: #3b82f6; }
.login-administrasi .login-btn.staff {
  background: #3b82f6;
}
.login-administrasi .login-btn.staff:hover:not(:disabled) {
  background: #2563eb;
}
.login-administrasi .login-footer .link { color: #3b82f6; }
.login-administrasi .form-group input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #bfdbfe 100%);
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
  transition: all 0.2s;
  outline: none;
}
.locked-input {
  background-color: #f8fafc;
  color: #64748b;
  cursor: not-allowed;
  pointer-events: none;
}
.error-msg { font-size: 0.875rem; color: #dc2626; margin-bottom: 1rem; }
.login-btn {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}
.login-btn:disabled { opacity: 0.85; cursor: wait; }
.btn-loading { display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center; width: 100%; }
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
