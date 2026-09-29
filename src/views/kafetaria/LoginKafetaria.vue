<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="logo">
          <span class="logo-icon">🍽️</span>
        </div>
        <h2>Portal Staff Kafetaria</h2>
        <p>Silakan masuk ke akun Anda</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="errorMsg" class="alert alert-error">
          {{ errorMsg }}
        </div>

        <div class="form-group">
          <label for="email">Alamat Email</label>
          <div class="input-wrapper">
            <span class="input-icon">✉️</span>
            <input 
              id="email" 
              v-model="email" 
              type="email" 
              placeholder="Masukkan email Anda" 
              required 
              :disabled="loading"
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Kata Sandi</label>
          <div class="input-wrapper">
            <span class="input-icon">🔒</span>
            <input 
              id="password" 
              v-model="password" 
              type="password" 
              placeholder="Masukkan kata sandi" 
              required 
              :disabled="loading"
            />
          </div>
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          <span v-else>Masuk ke Portal</span>
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

const email = ref('kafetaria@gmail.com');
const password = ref('Kafetaria123');
const loading = ref(false);
const errorMsg = ref('');

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMsg.value = 'Email dan password harus diisi';
    return;
  }

  loading.value = true;
  errorMsg.value = '';

  try {
    const response = await authApi.loginStaff(email.value, password.value);
    authStore.setAuth(response.token, response.user);
    router.push('/kafetaria/dashboard');
  } catch (error) {
    if (error.errors) {
      errorMsg.value = Object.values(error.errors).flat()[0];
    } else {
      errorMsg.value = error.message || 'Terjadi kesalahan saat login';
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f7fb 0%, #e2e8f0 100%);
  font-family: 'Inter', sans-serif;
  padding: 1rem;
}

.login-card {
  background: white;
  width: 100%;
  max-width: 440px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  padding: 3rem 2.5rem;
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo {
  width: 64px;
  height: 64px;
  background: #f1f5f9;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.logo-icon {
  font-size: 2rem;
}

h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

p {
  color: #64748b;
  margin: 0;
  font-size: 0.95rem;
}

.alert {
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert-error {
  background-color: #fef2f2;
  color: #ef4444;
  border: 1px solid #fecaca;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  font-weight: 500;
  color: #334155;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: #94a3b8;
  font-size: 1.1rem;
}

input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #1e293b;
  transition: all 0.2s;
  background: #f8fafc;
}

input:focus {
  outline: none;
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

input:disabled {
  background: #f1f5f9;
  cursor: not-allowed;
}

.btn-login {
  width: 100%;
  padding: 0.875rem;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 2rem;
}

.btn-login:hover:not(:disabled) {
  background: #d97706;
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
