<template>
  <div class="login-page login-asrama" :class="isPutri ? 'is-putri' : 'is-putra'">
    <div class="login-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Login {{ label }}</h1>
      <p class="login-subtitle">SLAPUR System — masuk dengan akun staff {{ label.toLowerCase() }}</p>

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
        <router-link :to="isPutri ? '/asramaputra' : '/asramaputri'" class="link">
          Login {{ isPutri ? 'Asrama Putra' : 'Asrama Putri' }}
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { authApi } from '@/api/auth';
import { useAuthStore } from '@/stores/auth';
import { useAsramaType, getStaffAsramaType } from '@/composables/useAsramaType';

const router = useRouter();
const authStore = useAuthStore();
const { type, isPutri, basePath, label } = useAsramaType();
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
      if (u.role && ['staff_asrama', 'admin', 'super_admin'].includes(u.role)) {
        const own = ['admin', 'super_admin'].includes(u.role) ? type.value : getStaffAsramaType(u);
        router.replace(`/asrama${own}/dashboard`);
      }
    }
  } catch (_) {}
});

async function handleLogin() {
  errorMsg.value = '';
  loading.value = true;
  try {
    const res = await authApi.loginStaff(form.email.trim(), form.password);
    
    if (!['staff_asrama', 'admin', 'super_admin'].includes(res.user.role)) {
       throw new Error('Anda tidak memiliki akses sebagai Staff Asrama.');
    }

    const isAdmin = ['admin', 'super_admin'].includes(res.user.role);
    const ownType = isAdmin ? type.value : getStaffAsramaType(res.user);
    if (ownType !== type.value) {
      const ownLabel = ownType === 'putri' ? 'Asrama Putri' : 'Asrama Putra';
      throw new Error(`Akun ini adalah akun staff ${ownLabel}. Silakan login melalui halaman /asrama${ownType}.`);
    }
    
    authStore.setAuth(res.token, { ...res.user, asrama_type: ownType });
    router.push(`${basePath.value}/dashboard`);
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
.login-asrama .login-title { color: #2563eb; }
.login-asrama .login-btn.staff { background: #2563eb; }
.login-asrama .login-btn.staff:hover:not(:disabled) { background: #1d4ed8; }
.login-asrama .login-footer .link { color: #2563eb; }
.login-asrama .form-group input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }

.login-asrama.is-putri { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #fbcfe8 100%); }
.login-asrama.is-putri .login-title { color: #db2777; }
.login-asrama.is-putri .login-btn.staff { background: #db2777; }
.login-asrama.is-putri .login-btn.staff:hover:not(:disabled) { background: #be185d; }
.login-asrama.is-putri .login-footer .link { color: #db2777; }
.login-asrama.is-putri .form-group input:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12); }
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

@media (max-width: 640px) {
  .login-card {
    padding: 1.75rem 1.25rem;
    border-radius: 16px;
  }
  .login-logo {
    width: 64px;
    height: 64px;
    margin-bottom: 1rem;
  }
}
</style>
