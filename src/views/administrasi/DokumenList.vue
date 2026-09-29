<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
    <!-- Header & Filter -->
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Antrean Verifikasi Dokumen</h2>
        <p class="text-sm text-gray-500">Tinjau dan validasi dokumen persyaratan yang diunggah pendaftar.</p>
      </div>
      
      <div class="flex items-center gap-2">
        <select v-model="filters.status" @change="fetchData" class="px-4 py-2 border border-gray-300 rounded bg-white text-sm">
          <option value="">Semua Status</option>
          <option value="menunggu">Menunggu</option>
          <option value="disetujui">Disetujui</option>
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
            <th class="px-6 py-4 font-semibold w-12 text-center">
              <input type="checkbox" @change="toggleAll" :checked="isAllSelected" class="rounded text-blue-600" />
            </th>
            <th class="px-6 py-4 font-semibold">Nama Siswa</th>
            <th class="px-6 py-4 font-semibold">Jenis Dokumen</th>
            <th class="px-6 py-4 font-semibold">Tanggal Unggah</th>
            <th class="px-6 py-4 font-semibold">Status</th>
            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat antrean...</td>
          </tr>
          <tr v-else-if="dokumen.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Antrean dokumen kosong. 🎉</td>
          </tr>
          <tr v-for="item in dokumen" :key="item.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 text-center">
              <input type="checkbox" v-model="selectedIds" :value="item.id" class="rounded text-blue-600" />
            </td>
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ item.student?.full_name }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="font-medium text-gray-800">{{ item.jenis_dokumen?.nama }}</div>
              <div v-if="item.jenis_dokumen?.wajib" class="text-xs text-red-500 font-medium">Wajib</div>
            </td>
            <td class="px-6 py-4 text-gray-600 text-xs">{{ new Date(item.created_at).toLocaleString() }}</td>
            <td class="px-6 py-4">
              <span v-if="item.status === 'menunggu'" class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium border border-yellow-200">⏳ Menunggu</span>
              <span v-else-if="item.status === 'disetujui'" class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium border border-green-200">✅ Disetujui</span>
              <span v-else class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium border border-red-200">❌ Ditolak</span>
            </td>
            <td class="px-6 py-4 text-right">
              <router-link :to="`/administrasi/dokumen/${item.id}`" class="text-blue-600 hover:text-blue-800 font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition">
                Tinjau ➔
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mass Action & Pagination -->
    <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-gray-50 rounded-b-xl">
      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-600">{{ selectedIds.length }} Terpilih</span>
        <button 
          v-if="selectedIds.length > 0" 
          @click="massApprove"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium transition shadow-sm"
        >
          ✅ Setujui Massal
        </button>
      </div>
      
      <div class="flex gap-2 text-sm text-gray-600 items-center">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">‹</button>
        <span class="px-3 py-1">Hal {{ currentPage }} / {{ lastPage }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50">›</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const dokumen = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);
const selectedIds = ref([]);

const filters = ref({
  status: 'menunggu'
});

const isAllSelected = computed(() => {
  return dokumen.value.length > 0 && selectedIds.value.length === dokumen.value.length;
});

const toggleAll = (e) => {
  if (e.target.checked) {
    selectedIds.value = dokumen.value.map(d => d.id);
  } else {
    selectedIds.value = [];
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/administrasi/dokumen', {
      params: {
        page: currentPage.value,
        status: filters.value.status
      }
    });
    
    dokumen.value = res.data.data;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
    selectedIds.value = []; // reset selection
  } catch (error) {
    console.error('Gagal mengambil antrean dokumen', error);
  } finally {
    loading.value = false;
  }
};

const massApprove = async () => {
  if(!confirm(`Yakin ingin menyetujui ${selectedIds.value.length} dokumen ini sekaligus?`)) return;
  
  // Karena belum ada endpoint khusus mass-approve, kita loop saja (bisa di-optimasi nanti)
  try {
    for (const id of selectedIds.value) {
      await axios.patch(`/api/administrasi/dokumen/${id}/verifikasi`, {
        status: 'disetujui'
      });
    }
    alert('Verifikasi massal berhasil diselesaikan!');
    fetchData();
  } catch(error) {
    alert('Terjadi kesalahan saat memproses sebagian dokumen.');
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
