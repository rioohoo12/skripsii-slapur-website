<template>
  <div class="list-page">
    <div class="list-header">
      <h2 class="list-title">Verifikasi Dokumen Siswa</h2>
      
      <div class="filter-group">
        <select v-model="filters.status" @change="fetchData" class="filter-input select-input">
          <option value="">Semua Status</option>
          <option value="menunggu">Menunggu</option>
          <option value="disetujui">Disetujui (Valid)</option>
          <option value="ditolak">Ditolak (Tidak Valid)</option>
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
            <th>Nama Pendaftar</th>
            <th>Jenis Dokumen</th>
            <th>Tanggal Unggah</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="empty-state">Memuat dokumen...</td>
          </tr>
          <tr v-else-if="dokumen.length === 0">
            <td colspan="6" class="empty-state">Tidak ada antrean dokumen.</td>
          </tr>
          <tr v-for="(item, index) in dokumen" :key="item.id">
            <td class="text-muted">{{ index + 1 }}</td>
            <td class="font-medium text-dark">{{ item.student?.full_name || 'Tanpa Nama' }}</td>
            <td class="font-medium text-dark">{{ item.jenis_dokumen?.nama || 'Dokumen' }}</td>
            <td class="text-muted">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
            <td>
              <span class="status-badge" :class="getStatusBadge(item.status)">
                {{ formatStatus(item.status) }}
              </span>
            </td>
            <td class="text-right">
              <router-link :to="`/administrasi/dokumen/${item.id}`" class="action-btn">
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
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const dokumen = ref([]);
const loading = ref(true);

const filters = ref({
  status: ''
});

const getStatusBadge = (status) => {
  if (status === 'disetujui') return 'badge-success';
  if (status === 'ditolak') return 'badge-danger';
  return 'badge-warning';
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
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      params: {
        status: filters.value.status
      }
    });
    dokumen.value = res.data.data || [];
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
  min-width: 200px;
}
.filter-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: #fff;
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

/* Buttons */
.action-btn {
  display: inline-block;
  background: #dbeafe;
  color: #1d4ed8;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  transition: all 0.2s;
}
.action-btn:hover {
  background: #bfdbfe;
}
</style>
