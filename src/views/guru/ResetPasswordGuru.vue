<template>
  <div class="login-page login-guru">
    <div class="login-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Atur Ulang Password</h1>
      <p class="login-subtitle">Masukkan password baru untuk akun guru</p>

      <form class="login-form" @submit.prevent="handleSubmit">
        <div class="form-group" v-if="!emailFromQuery">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Email akun guru"
            required
          />
        </div>
        <div class="form-group">
          <label for="password">Password baru</label>
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
          <label for="confirmPassword">Konfirmasi password baru</label>
          <input
            id="confirmPassword"
            v-model="form.confirmPassword"
            type="password"
            placeholder="Ulangi password"
            required
            minlength="6"
          />
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        <button type="submit" class="login-btn guru" :disabled="loading">
          <span v-if="loading" class="btn-loading">
            <span class="spinner"></span>
            Memproses...
          </span>
          <span v-else>Reset password</span>
        </button>
      </form>
      <p class="login-footer">
        <router-link to="/guru" class="link">Kembali ke Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { authApi } from '@/api/auth';

const router = useRouter();
const route = useRoute();
const form = reactive({
  email: '',
  token: '',
  password: '',
  confirmPassword: '',
});
const loading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const emailFromQuery = computed(() => !!route.query.email);

onMounted(() => {
  if (route.query.token) form.token = route.query.token;
  if (route.query.email) form.email = route.query.email;
});

async function handleSubmit() {
  errorMsg.value = '';
  successMsg.value = '';
  if (!form.token) {
    errorMsg.value = 'Token tidak ada. Minta link reset dari halaman Lupa password.';
    return;
  }
  if (form.password !== form.confirmPassword) {
    errorMsg.value = 'Konfirmasi password tidak sama.';
    return;
  }
  if (form.password.length < 6) {
    errorMsg.value = 'Password minimal 6 karakter.';
    return;
  }
  loading.value = true;
  try {
    await authApi.resetPasswordGuru({
      email: form.email.trim(),
      token: form.token,
      password: form.password,
      confirmPassword: form.confirmPassword,
    });
    successMsg.value = 'Password berhasil direset. Mengalihkan ke login...';
    setTimeout(() => router.push('/guru'), 1500);
  } catch (e) {
    errorMsg.value = e?.message || 'Token tidak valid atau sudah kadaluarsa.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-guru .login-title { color: #5b21b6; }
.login-guru .login-btn.guru { background: #5b21b6; }
.login-guru .login-btn.guru:hover:not(:disabled) { background: #7c3aed; }
.login-guru .login-footer .link { color: #5b21b6; font-weight: 600; text-decoration: none; }
.login-guru .form-group input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
.error-msg { font-size: 0.875rem; color: #dc2626; margin-bottom: 1rem; }
.success-msg { font-size: 0.875rem; color: #059669; margin-bottom: 1rem; }
</style>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #e0e7ff 100%);
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
.login-subtitle { font-size: 0.95rem; color: #64748b; margin-bottom: 1.25rem; }
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
.login-footer { margin-top: 1.25rem; font-size: 0.9rem; color: #64748b; }
</style>
