<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Manajemen Tagihan</h2>
        <p class="text-sm text-gray-500">Buat dan pantau tagihan (SPP, Pendaftaran, Asrama) siswa.</p>
      </div>
      <div class="flex gap-2">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm transition">
          + Buat Tagihan Baru
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto flex-1 p-6">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
            <th class="px-4 py-3 font-semibold">Nama Siswa</th>
            <th class="px-4 py-3 font-semibold">Jenis Tagihan</th>
            <th class="px-4 py-3 font-semibold text-right">Nominal (Rp)</th>
            <th class="px-4 py-3 font-semibold">Jatuh Tempo</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Memuat data tagihan...</td>
          </tr>
          <tr v-else-if="tagihans.length === 0">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada tagihan.</td>
          </tr>
          <tr v-for="tagihan in tagihans" :key="tagihan.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900">{{ tagihan.student?.full_name }}</td>
            <td class="px-4 py-3 text-gray-700 capitalize">{{ tagihan.jenis }}</td>
            <td class="px-4 py-3 text-right text-gray-800 font-medium">{{ formatRupiah(tagihan.nominal) }}</td>
            <td class="px-4 py-3 text-gray-600">{{ tagihan.jatuh_tempo }}</td>
            <td class="px-4 py-3">
              <span v-if="tagihan.status === 'belum_bayar'" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Belum Bayar</span>
              <span v-else class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Lunas</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button class="text-blue-600 hover:underline text-xs bg-blue-50 px-2 py-1 rounded">Ingatkan via Email</button>
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

const tagihans = ref([]);
const loading = ref(true);

const formatRupiah = (angka) => {
  if (!angka) return 0;
  return new Intl.NumberFormat('id-ID').format(angka);
};

const fetchTagihan = async () => {
  try {
    const res = await axios.get('/api/administrasi/tagihan');
    tagihans.value = res.data.data;
  } catch(e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchTagihan();
});
</script>
