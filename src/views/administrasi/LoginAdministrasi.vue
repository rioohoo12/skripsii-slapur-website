<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Login Administrasi</h2>
        <p class="text-sm text-gray-600 mt-2">Masuk ke panel manajemen staff</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Staff</label>
          <input 
            type="email" 
            v-model="email" 
            required 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="admin.sekolah@sekolah.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <input 
            type="password" 
            v-model="password" 
            required 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="••••••••"
          />
        </div>

        <div v-if="error" class="p-3 bg-red-50 text-red-600 text-sm rounded-lg">
          {{ error }}
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors disabled:opacity-50"
        >
          <span v-if="loading">Memproses...</span>
          <span v-else>Masuk Sekarang</span>
        </button>
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
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    // Meminta login ke route API biasa/administrasi
    // Untuk saat ini fallback ke login bawaan /api/login karena belum ada AdministrasiController
    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
      device_name: 'administrasi_web'
    });

    if (res.data && res.data.token) {
      authStore.setAuth(res.data.token, res.data.user);
      
      // Pastikan rolenya benar
      if (!authStore.isStaffAdministrasi) {
        authStore.logout();
        error.value = 'Akses ditolak. Anda bukan Staff Administrasi.';
        return;
      }
      
      router.push('/administrasi/dashboard');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Login gagal. Periksa kembali email dan password Anda.';
  } finally {
    loading.value = false;
  }
};
</script>
