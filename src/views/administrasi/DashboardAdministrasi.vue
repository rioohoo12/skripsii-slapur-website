<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-gray-800">Ringkasan Sistem</h2>
      <button @click="fetchDashboard" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded shadow text-sm font-medium transition-colors">
        Refresh Data
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Card Pendaftar Baru -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
        <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        </div>
        <div>
          <p class="text-sm text-gray-500 font-medium">Pendaftar Baru</p>
          <h3 class="text-2xl font-bold text-gray-800">{{ stats.recent_applicants || 0 }}</h3>
        </div>
      </div>
      
      <!-- Card Dokumen -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
        <div class="w-12 h-12 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <div>
          <p class="text-sm text-gray-500 font-medium">Dokumen Menunggu</p>
          <h3 class="text-2xl font-bold text-gray-800">{{ stats.waiting_docs || 0 }}</h3>
        </div>
      </div>
      
      <!-- Card Pembayaran -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
        <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        </div>
        <div>
          <p class="text-sm text-gray-500 font-medium">Pembayaran Tertunda</p>
          <h3 class="text-2xl font-bold text-gray-800">{{ stats.waiting_payments || 0 }}</h3>
        </div>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
  waiting_docs: 0,
  waiting_payments: 0,
  recent_applicants: 0
});

const fetchDashboard = async () => {
  try {
    const res = await axios.get('/api/v1/staff/dashboard');
    stats.value = res.data.stats;
  } catch (error) {
    console.error("Gagal mengambil data dashboard", error);
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>
