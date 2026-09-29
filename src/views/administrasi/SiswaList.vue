<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
    <!-- Header & Filter -->
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Manajemen Data Siswa</h2>
        <p class="text-sm text-gray-500">Kelola dan pantau seluruh pendaftar atau siswa aktif.</p>
      </div>
      
      <div class="flex items-center gap-2">
        <input 
          v-model="filters.search" 
          @keyup.enter="fetchData"
          type="text" 
          placeholder="Cari nama atau no. daftar..." 
          class="px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 w-64 text-sm"
        />
        <select v-model="filters.status" @change="fetchData" class="px-4 py-2 border border-gray-300 rounded bg-white text-sm">
          <option value="">Semua Status</option>
          <option value="calon">Calon Siswa</option>
          <option value="dokumen">Dokumen</option>
          <option value="pembayaran">Pembayaran</option>
          <option value="kamar">Pemilihan Kamar</option>
          <option value="aktif">Siswa Aktif</option>
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
            <th class="px-6 py-4 font-semibold">Nama / Kontak</th>
            <th class="px-6 py-4 font-semibold">No. Daftar</th>
            <th class="px-6 py-4 font-semibold">Tingkat</th>
            <th class="px-6 py-4 font-semibold">Asal Pendaftaran</th>
            <th class="px-6 py-4 font-semibold">Status Tahapan</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat data...</td>
          </tr>
          <tr v-else-if="siswa.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data siswa.</td>
          </tr>
          <tr v-for="item in siswa" :key="item.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ item.full_name }}</div>
              <div class="text-xs text-gray-500">{{ item.email_address || 'Tidak ada email' }}</div>
            </td>
            <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ item.nomor_pendaftaran || '-' }}</td>
            <td class="px-6 py-4 text-gray-600">Kelas {{ item.kelas_yang_didaftar || '-' }}</td>
            <td class="px-6 py-4">
              <span v-if="item.source === 'chatbot'" class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">🤖 Chatbot</span>
              <span v-else class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">🌐 Web</span>
            </td>
            <td class="px-6 py-4">
              <span :class="getStatusBadgeClass(item.status_pendaftaran)" class="px-2 py-1 rounded-full text-xs font-medium border">
                {{ item.status_pendaftaran || 'calon' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <router-link :to="`/administrasi/siswa/${item.id}`" class="text-blue-600 hover:text-blue-800 font-medium">
                Detail ➔
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination (Sederhana) -->
    <div class="p-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-600 bg-gray-50 rounded-b-xl">
      <div>Total: <span class="font-bold">{{ totalData }}</span> data</div>
      <div class="flex gap-2">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">Sebelumnnya</button>
        <span class="px-3 py-1">Hal {{ currentPage }} / {{ lastPage }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">Selanjutnya</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const siswa = ref([]);
const loading = ref(true);
const totalData = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  search: '',
  status: ''
});

const getStatusBadgeClass = (status) => {
  if (status === 'calon') return 'bg-gray-100 text-gray-700 border-gray-200';
  if (status === 'dokumen' || status === 'pembayaran') return 'bg-yellow-50 text-yellow-700 border-yellow-200';
  if (status === 'kamar') return 'bg-blue-50 text-blue-700 border-blue-200';
  if (status === 'aktif') return 'bg-green-50 text-green-700 border-green-200';
  return 'bg-gray-50 text-gray-700 border-gray-200';
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/administrasi/siswa', {
      params: {
        page: currentPage.value,
        search: filters.value.search,
        status_pendaftaran: filters.value.status
      }
    });
    
    siswa.value = res.data.data;
    totalData.value = res.data.total;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
  } catch (error) {
    console.error('Gagal mengambil data siswa', error);
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
