<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
    <!-- Header & Filter -->
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Verifikasi Pembayaran</h2>
        <p class="text-sm text-gray-500">Kelola konfirmasi pembayaran masuk, transfer manual, dan otomatisasi Midtrans.</p>
      </div>
      
      <div class="flex items-center gap-2">
        <input 
          v-model="filters.search" 
          @keyup.enter="fetchData"
          type="text" 
          placeholder="Cari order ID atau nama..." 
          class="px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 w-64 text-sm"
        />
        <select v-model="filters.status" @change="fetchData" class="px-4 py-2 border border-gray-300 rounded bg-white text-sm">
          <option value="">Semua Status</option>
          <option value="menunggu">Menunggu / Pending</option>
          <option value="terverifikasi">Terverifikasi</option>
          <option value="ditolak">Ditolak</option>
        </select>
        <button @click="fetchData" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm transition font-medium">
          Filter
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto flex-1">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
            <th class="px-6 py-4 font-semibold">Nama Siswa / Tagihan</th>
            <th class="px-6 py-4 font-semibold">Order ID</th>
            <th class="px-6 py-4 font-semibold">Metode</th>
            <th class="px-6 py-4 font-semibold text-right">Nominal (Rp)</th>
            <th class="px-6 py-4 font-semibold text-center">Status</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat data pembayaran...</td>
          </tr>
          <tr v-else-if="pembayaran.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada riwayat pembayaran yang cocok.</td>
          </tr>
          <tr v-for="item in pembayaran" :key="item.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <div class="font-bold text-gray-900">{{ item.tagihan?.student?.full_name || 'Tidak diketahui' }}</div>
              <div class="text-xs text-gray-500 uppercase tracking-wide">{{ item.tagihan?.jenis }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="font-mono text-xs text-gray-600">{{ item.midtrans_order_id || '-' }}</div>
              <div class="text-[10px] text-gray-400 mt-1">{{ new Date(item.created_at).toLocaleString() }}</div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium border border-gray-200">
                {{ item.metode || 'MANUAL' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right font-medium text-gray-800">
              {{ formatRupiah(item.tagihan?.nominal) }}
            </td>
            <td class="px-6 py-4 text-center">
              <span v-if="item.status === 'menunggu'" class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium border border-yellow-200">⏳ Pending</span>
              <span v-else-if="item.status === 'terverifikasi'" class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium border border-green-200">✅ Valid</span>
              <span v-else class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium border border-red-200">❌ Gagal</span>
            </td>
            <td class="px-6 py-4 text-right">
              <router-link :to="`/administrasi/pembayaran/${item.id}`" class="text-blue-600 hover:text-blue-800 font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition">
                Rincian ➔
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-gray-50 rounded-b-xl text-sm text-gray-600">
      <div>Total Data: <span class="font-bold">{{ totalData }}</span></div>
      <div class="flex gap-2">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">‹</button>
        <span class="px-3 py-1">Hal {{ currentPage }} / {{ lastPage }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">›</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const pembayaran = ref([]);
const loading = ref(true);
const totalData = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  search: '',
  status: ''
});

const formatRupiah = (angka) => {
  if (!angka) return 0;
  return new Intl.NumberFormat('id-ID').format(angka);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/administrasi/pembayaran', {
      params: {
        page: currentPage.value,
        search: filters.value.search,
        status: filters.value.status
      }
    });
    
    pembayaran.value = res.data.data;
    totalData.value = res.data.total;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
  } catch (error) {
    console.error('Gagal mengambil data pembayaran', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page < 1 || page > lastPage.value) return;
  currentPage.value = page;
  fetchData();
};

onMounted(() => {
  fetchData();
});
</script>
