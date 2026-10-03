<template>
  <div class="bg-white rounded border border-gray-200 flex flex-col">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between">
      <h2 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Daftar Pendaftar</h2>
      
      <div class="flex items-center gap-2 w-full md:w-auto">
        <input 
          v-model="filters.search" 
          @keyup.enter="fetchData"
          type="text" 
          placeholder="Cari nama / no. pendaftaran..." 
          class="px-4 py-2 bg-gray-100 border-none rounded text-sm w-full md:w-64 focus:ring-2 focus:ring-gray-300 outline-none"
        />
        <select v-model="filters.status" @change="fetchData" class="px-4 py-2 bg-gray-100 border-none rounded text-sm focus:ring-2 focus:ring-gray-300 outline-none">
          <option value="">Semua Status</option>
          <option value="calon">Baru</option>
          <option value="dokumen">Dokumen</option>
          <option value="pembayaran">Pembayaran</option>
          <option value="aktif">Diterima</option>
        </select>
        <button @click="fetchData" class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
          Filter
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
            <th class="px-6 py-4 font-semibold w-16">No</th>
            <th class="px-6 py-4 font-semibold">No. Pendaftaran</th>
            <th class="px-6 py-4 font-semibold">Nama Pendaftar</th>
            <th class="px-6 py-4 font-semibold">Tanggal Daftar</th>
            <th class="px-6 py-4 font-semibold">Status</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-sm">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat data...</td>
          </tr>
          <tr v-else-if="siswa.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data pendaftar.</td>
          </tr>
          <tr v-for="(item, index) in siswa" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-gray-500">{{ (currentPage - 1) * 10 + index + 1 }}</td>
            <td class="px-6 py-4 text-gray-900 font-medium">{{ item.nomor_pendaftaran || '-' }}</td>
            <td class="px-6 py-4 text-gray-900">{{ item.full_name }}</td>
            <td class="px-6 py-4 text-gray-600">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
            <td class="px-6 py-4">
              <span :class="getStatusBadgeClass(item.status_pendaftaran)" class="px-3 py-1 rounded text-xs font-semibold">
                {{ formatStatus(item.status_pendaftaran) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <router-link :to="`/staff/pendaftaran/${item.id}`" class="text-blue-600 hover:underline font-medium">
                Detail
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const siswa = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  search: '',
  status: ''
});

const getStatusBadgeClass = (status) => {
  if (status === 'aktif' || status === 'diterima') return 'bg-green-100 text-green-700';
  if (status === 'ditolak') return 'bg-red-100 text-red-700';
  return 'bg-yellow-100 text-yellow-700';
};

const formatStatus = (status) => {
  if (!status) return 'Menunggu';
  if (status === 'calon') return 'Baru';
  if (status === 'dokumen') return 'Proses Dokumen';
  if (status === 'pembayaran') return 'Proses Bayar';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/v1/staff/students', {
      params: {
        page: currentPage.value,
        search: filters.value.search,
        status_pendaftaran: filters.value.status
      }
    });
    
    siswa.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
  } catch (error) {
    console.error('Gagal mengambil data', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
