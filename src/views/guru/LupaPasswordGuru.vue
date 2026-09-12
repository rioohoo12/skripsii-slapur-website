<template>
  <div class="login-page login-guru">
    <div class="login-card">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Lupa Password</h1>
      <p class="login-subtitle">Masukkan email akun guru untuk reset password</p>

      <form v-if="!successMsg" class="login-form" @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Masukkan email terdaftar"
            required
          />
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <button type="submit" class="login-btn guru" :disabled="loading">
          <span v-if="loading" class="btn-loading">
            <span class="spinner"></span>
            Memproses...
          </span>
          <span v-else>Kirim link reset</span>
        </button>
      </form>
      <template v-else>
        <p class="success-msg">{{ successMsg }}</p>
        <p v-if="resetLink" class="reset-link-msg">
          <router-link :to="resetLink" class="link">Atur ulang password di sini</router-link>
        </p>
        <p class="login-footer">
          <router-link to="/guru" class="link">Kembali ke Login</router-link>
        </p>
      </template>
      <p v-if="!successMsg" class="login-footer">
        Ingat password?
        <router-link to="/guru" class="link">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { authApi } from '@/api/auth';

const form = reactive({ email: '' });
const loading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');
const resetToken = ref('');
const resetEmail = ref('');

const resetLink = computed(() => {
  if (!resetToken.value || !resetEmail.value) return null;
  return {
    path: '/guru/reset-password',
    query: { token: resetToken.value, email: resetEmail.value },
  };
});

async function handleSubmit() {
  errorMsg.value = '';
  successMsg.value = '';
  loading.value = true;
  try {
    const res = await authApi.forgotPasswordGuru(form.email.trim());
    successMsg.value = res.message || 'Jika email terdaftar, cek tautan reset.';
    if (res.reset_token && res.email) {
      resetToken.value = res.reset_token;
      resetEmail.value = res.email;
    }
  } catch (e) {
    errorMsg.value = e?.message || 'Gagal mengirim. Coba lagi.';
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
.login-guru .login-footer .link:hover { text-decoration: underline; }
.login-guru .form-group input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
.error-msg { font-size: 0.875rem; color: #dc2626; margin-bottom: 1rem; }
.success-msg { font-size: 0.9rem; color: #059669; margin-bottom: 1rem; text-align: center; }
.reset-link-msg { margin: 1rem 0; }
.reset-link-msg .link { color: #5b21b6; font-weight: 600; }
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
