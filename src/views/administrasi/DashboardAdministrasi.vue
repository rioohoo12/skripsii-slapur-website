<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Ringkasan Sistem</h2>
        <p class="text-sm text-gray-500">Pantau performa dan tugas harian administrasi.</p>
      </div>
      <button @click="fetchDashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm transition">
        🔄 Segarkan Data
      </button>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center text-xl">📄</div>
        <div>
          <div class="text-sm text-gray-500 font-medium">Dokumen Menunggu</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.waiting_docs }}</div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl">💳</div>
        <div>
          <div class="text-sm text-gray-500 font-medium">Pembayaran Menunggu</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.waiting_payments }}</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl">💰</div>
        <div>
          <div class="text-sm text-gray-500 font-medium">Pemasukan Bulan Ini</div>
          <div class="text-2xl font-bold text-gray-800">Rp {{ formatRupiah(stats.monthly_revenue) }}</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
        <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-xl">🤖</div>
        <div>
          <div class="text-sm text-gray-500 font-medium">Pendaftar Baru</div>
          <div class="text-2xl font-bold text-gray-800">{{ stats.recent_applicants }}</div>
        </div>
      </div>
    </div>

    <!-- Grafik dan Daftar Tindakan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Grafik (Kiri, lebih lebar) -->
      <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Statistik Pembayaran</h3>
        <div class="h-80 flex items-center justify-center bg-gray-50 rounded border border-dashed border-gray-200">
          <apexchart 
            v-if="chartOptions && series"
            type="donut" 
            height="320" 
            :options="chartOptions" 
            :series="series"
          ></apexchart>
          <p v-else class="text-gray-400">Memuat grafik...</p>
        </div>
      </div>

      <!-- Perlu Tindakan (Kanan) -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-lg font-bold text-gray-800 mb-4">🚨 Perlu Tindakan</h3>
        
        <div class="flex-1 overflow-y-auto space-y-4">
          <div v-if="actionNeeded.docs.length === 0 && actionNeeded.payments.length === 0" class="text-center text-gray-500 py-8">
            Hore! Semua tugas telah diselesaikan. 🎉
          </div>
          
          <template v-for="doc in actionNeeded.docs" :key="'doc-'+doc.id">
            <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded text-sm flex justify-between items-center">
              <div>
                <span class="font-bold text-red-700">Verifikasi Dokumen</span><br/>
                <span class="text-gray-600">{{ doc.student?.full_name || 'NN' }} - {{ doc.jenis_dokumen?.nama }}</span>
              </div>
              <router-link :to="`/administrasi/dokumen/${doc.id}`" class="text-blue-600 hover:underline">Proses ➔</router-link>
            </div>
          </template>

          <template v-for="pay in actionNeeded.payments" :key="'pay-'+pay.id">
            <div class="bg-orange-50 border-l-4 border-orange-500 p-3 rounded text-sm flex justify-between items-center">
              <div>
                <span class="font-bold text-orange-700">Verifikasi Pembayaran</span><br/>
                <span class="text-gray-600">{{ pay.tagihan?.student?.full_name || 'NN' }} - Rp {{ formatRupiah(pay.tagihan?.nominal) }}</span>
              </div>
              <router-link :to="`/administrasi/pembayaran/${pay.id}`" class="text-blue-600 hover:underline">Proses ➔</router-link>
            </div>
          </template>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import VueApexCharts from 'vue3-apexcharts';

// Registrasi komponen apexchart secara lokal di setup script
const apexchart = VueApexCharts;

const stats = ref({
  waiting_docs: 0,
  waiting_payments: 0,
  monthly_revenue: 0,
  recent_applicants: 0
});

const actionNeeded = ref({
  docs: [],
  payments: []
});

const series = ref([]);
const chartOptions = ref(null);

const formatRupiah = (angka) => {
  if (!angka) return 0;
  return new Intl.NumberFormat('id-ID').format(angka);
};

const fetchDashboard = async () => {
  try {
    const res = await axios.get('/api/administrasi/dashboard');
    stats.value = res.data.stats;
    actionNeeded.value = res.data.action_needed;
    
    // Setup Chart
    const payStatus = res.data.charts.payment_status;
    series.value = [payStatus.terverifikasi || 0, payStatus.menunggu || 0, payStatus.ditolak || 0];
    chartOptions.value = {
      chart: { type: 'donut' },
      labels: ['Lunas / Terverifikasi', 'Menunggu', 'Gagal / Ditolak'],
      colors: ['#10B981', '#F59E0B', '#EF4444'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: true }
    };
  } catch (error) {
    console.error("Gagal mengambil data dashboard", error);
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>
