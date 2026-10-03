<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-100">
      
      <!-- Header -->
      <div class="flex flex-col items-center">
        <!-- Logo Sekolah Placeholder -->
        <div class="h-16 w-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
        </div>
        <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900">
          Staff Administrasi
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Masuk ke sistem manajemen akademik
        </p>
      </div>

      <!-- Alert Pesan Error -->
      <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <!-- Email Input -->
          <div>
            <label for="email-address" class="block text-sm font-medium text-gray-700">Alamat Email</label>
            <div class="mt-1">
              <input id="email-address" name="email" type="email" autocomplete="email" required v-model="email"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-50"
                placeholder="administrasi@gmail.com" />
            </div>
            <p v-if="emailError" class="mt-2 text-sm text-red-600">{{ emailError }}</p>
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="mt-1 relative">
              <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required v-model="password"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-gray-50"
                placeholder="••••••••" />
              
              <!-- Show/Hide Password Toggle -->
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" @click="showPassword = !showPassword" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                  <svg v-if="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>
            <p v-if="passwordError" class="mt-2 text-sm text-red-600">{{ passwordError }}</p>
          </div>
        </div>

        <div>
          <button type="submit" :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:bg-blue-400 disabled:cursor-not-allowed">
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Memproses...' : 'Login' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAdministrasiAuthStore } from '@/stores/administrasiAuth';
import axios from 'axios';

const router = useRouter();
const authStore = useAdministrasiAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const error = ref('');
const emailError = ref('');
const passwordError = ref('');

const validateForm = () => {
  let isValid = true;
  emailError.value = '';
  passwordError.value = '';

  if (!email.value) {
    emailError.value = 'Email wajib diisi';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    emailError.value = 'Format email tidak valid';
    isValid = false;
  }

  if (!password.value) {
    passwordError.value = 'Password wajib diisi';
    isValid = false;
  } else if (password.value.length < 6) {
    passwordError.value = 'Password minimal 6 karakter';
    isValid = false;
  }

  return isValid;
};

const handleLogin = async () => {
  if (!validateForm()) return;

  loading.value = true;
  error.value = '';
  
  try {
    const res = await axios.post('/api/v1/auth/login', {
      email: email.value,
      password: password.value,
      device_name: 'administrasi_web'
    });

    if (res.data && res.data.token) {
      if (res.data.user?.status !== 'aktif') {
        error.value = 'Akun Anda sedang tidak aktif. Hubungi administrator.';
        return;
      }
      
      authStore.setAuth(res.data.token, res.data.user);
      
      if (!authStore.isStaffAdministrasi) {
        authStore.logout();
        error.value = 'Akses ditolak. Anda bukan Staff Administrasi.';
        return;
      }
      
      router.push('/staff/dashboard');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Email atau password salah. Silakan coba lagi.';
  } finally {
    loading.value = false;
  }
};
</script>
