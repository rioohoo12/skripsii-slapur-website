<template>
  <div class="list-page">
    <div class="list-header">
      <h2 class="list-title">Data Pendaftaran Siswa</h2>
      
      <div class="filter-group">
        <input 
          v-model="filters.search" 
          @keyup.enter="fetchData"
          type="text" 
          placeholder="Cari nama / no. pendaftaran..." 
          class="filter-input search-input"
        />
        <select v-model="filters.status" @change="fetchData" class="filter-input select-input">
          <option value="">Semua Status</option>
          <option value="calon">Baru</option>
          <option value="dokumen">Dokumen</option>
          <option value="pembayaran">Pembayaran</option>
          <option value="aktif">Diterima</option>
        </select>
        <button @click="fetchData" class="filter-btn">
          Terapkan
        </button>
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-no">No</th>
            <th>No. Pendaftaran</th>
            <th>Nama Pendaftar</th>
            <th>Tanggal Daftar</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" class="empty-state">Memuat data...</td>
          </tr>
          <tr v-else-if="siswa.length === 0">
            <td colspan="5" class="empty-state">Belum ada data pendaftar.</td>
          </tr>
          <tr v-for="(item, index) in siswa" :key="item.id">
            <td class="text-muted">{{ (currentPage - 1) * 10 + index + 1 }}</td>
            <td class="font-medium text-dark">{{ item.nomor_pendaftaran || '-' }}</td>
            <td class="font-medium text-dark">{{ item.full_name }}</td>
            <td class="text-muted">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
            <td>
              <span class="status-badge" :class="getStatusBadgeClass(item.status_pendaftaran)">
                {{ formatStatus(item.status_pendaftaran) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Simple Pagination Controls if needed -->
    <div class="pagination-footer" v-if="lastPage > 1">
      <button :disabled="currentPage === 1" @click="changePage(currentPage - 1)" class="page-btn">Sebelumnya</button>
      <span class="page-info">Halaman {{ currentPage }} dari {{ lastPage }}</span>
      <button :disabled="currentPage === lastPage" @click="changePage(currentPage + 1)" class="page-btn">Selanjutnya</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const siswa = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  search: '',
  status: ''
});

const getStatusBadgeClass = (status) => {
  if (status === 'aktif' || status === 'diterima') return 'badge-success';
  if (status === 'ditolak') return 'badge-danger';
  if (status === 'calon') return 'badge-info';
  return 'badge-warning';
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
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      params: {
        page: currentPage.value,
        search: filters.value.search,
        status_pendaftaran: filters.value.status
      }
    });
    
    siswa.value = res.data.data || [];
    currentPage.value = res.data.current_page || 1;
    lastPage.value = res.data.last_page || 1;
  } catch (error) {
    console.error('Gagal mengambil data', error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    currentPage.value = page;
    fetchData();
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.list-page {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.list-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
@media (min-width: 768px) {
  .list-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}
.list-title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
}
.filter-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
}
@media (min-width: 768px) {
  .filter-group {
    width: auto;
  }
}
.filter-input {
  padding: 0.6rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: inherit;
  color: #334155;
  background: #f8fafc;
  outline: none;
  transition: all 0.2s;
}
.filter-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: #fff;
}
.search-input {
  flex: 1;
  min-width: 200px;
}
.filter-btn {
  background: #0f172a;
  color: #fff;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}
.filter-btn:hover {
  background: #334155;
}

.table-container {
  overflow-x: auto;
  width: 100%;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}
.data-table th {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
}
.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.95rem;
}
.data-table tr:hover td {
  background: #f8fafc;
}
.data-table tr:last-child td {
  border-bottom: none;
}
.w-no {
  width: 60px;
}
.text-right {
  text-align: right;
}
.text-muted {
  color: #64748b;
}
.text-dark {
  color: #0f172a;
}
.font-medium {
  font-weight: 600;
}
.empty-state {
  text-align: center;
  padding: 3rem 1.5rem !important;
  color: #94a3b8;
  font-style: italic;
}

/* Badges */
.status-badge {
  display: inline-flex;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}
.badge-success { background: #dcfce7; color: #166534; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef9c3; color: #854d0e; }
.badge-info { background: #dbeafe; color: #1e40af; }

.action-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  transition: background 0.2s;
}
.action-link:hover {
  background: #eff6ff;
  text-decoration: underline;
}

.pagination-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
}
.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #cbd5e1;
  background: #fff;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}
.page-btn:hover:not(:disabled) {
  background: #f1f5f9;
}
.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.page-info {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}
</style>
