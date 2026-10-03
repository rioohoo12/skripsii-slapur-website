<template>
  <div class="bg-white rounded border border-gray-200 flex flex-col">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between">
      <h2 class="text-xl font-bold text-gray-800 mb-4 md:mb-0">Verifikasi Dokumen</h2>
      
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select v-model="filters.status" @change="fetchData" class="px-4 py-2 bg-gray-100 border-none rounded text-sm focus:ring-2 focus:ring-gray-300 outline-none w-full md:w-48">
          <option value="">Semua Status</option>
          <option value="menunggu">Menunggu</option>
          <option value="disetujui">Disetujui (Valid)</option>
          <option value="ditolak">Ditolak (Tidak Valid)</option>
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
            <th class="px-6 py-4 font-semibold">Nama Pendaftar</th>
            <th class="px-6 py-4 font-semibold">Jenis Dokumen</th>
            <th class="px-6 py-4 font-semibold">Tanggal Unggah</th>
            <th class="px-6 py-4 font-semibold">Status</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-sm">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat dokumen...</td>
          </tr>
          <tr v-else-if="dokumen.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada antrean dokumen.</td>
          </tr>
          <tr v-for="(item, index) in dokumen" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-gray-500">{{ index + 1 }}</td>
            <td class="px-6 py-4 text-gray-900 font-medium">{{ item.student?.full_name }}</td>
            <td class="px-6 py-4 text-gray-900">{{ item.jenis_dokumen?.nama }}</td>
            <td class="px-6 py-4 text-gray-600">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
            <td class="px-6 py-4">
              <span :class="getStatusBadge(item.status)" class="px-3 py-1 rounded text-xs font-semibold">
                {{ formatStatus(item.status) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <router-link :to="`/staff/dokumen/${item.id}`" class="bg-blue-100 text-blue-700 px-3 py-1.5 rounded text-xs font-medium hover:bg-blue-200 transition-colors">
                Lihat & Verifikasi
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

const dokumen = ref([]);
const loading = ref(true);

const filters = ref({
  status: 'menunggu'
});

const getStatusBadge = (status) => {
  if (status === 'disetujui') return 'bg-green-100 text-green-700';
  if (status === 'ditolak') return 'bg-red-100 text-red-700';
  return 'bg-yellow-100 text-yellow-700';
};

const formatStatus = (status) => {
  if (status === 'disetujui') return 'Valid';
  if (status === 'ditolak') return 'Ditolak';
  return 'Menunggu';
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/v1/staff/dokumen', {
      params: {
        status: filters.value.status
      }
    });
    dokumen.value = res.data.data;
  } catch (error) {
    console.error('Gagal mengambil dokumen', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
