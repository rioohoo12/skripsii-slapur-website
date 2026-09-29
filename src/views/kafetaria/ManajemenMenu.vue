<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen Menu</h1>
        <p class="page-subtitle">Kelola jadwal dan daftar menu makan siswa</p>
      </div>
      <button class="btn btn-primary" @click="openModal()">
        <span>+ Tambah Menu</span>
      </button>
    </div>

    <div class="content-card">
      <div class="filters">
        <div class="form-group">
          <label>Filter Tanggal</label>
          <input type="date" v-model="filterDate" @change="fetchMenus" />
        </div>
        <button class="btn btn-outline" @click="resetFilter" v-if="filterDate">
          Reset Filter
        </button>
      </div>

      <div class="table-responsive">
        <table class="menu-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Sesi Makan</th>
              <th>Menu Detail</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="text-center">Memuat data...</td>
            </tr>
            <tr v-else-if="menus.length === 0">
              <td colspan="4" class="text-center text-muted">Belum ada menu yang dijadwalkan</td>
            </tr>
            <tr v-for="menu in menus" :key="menu.id" v-else>
              <td>{{ formatDate(menu.date_served) }}</td>
              <td>
                <span class="badge" :class="menu.meal_time.toLowerCase()">
                  {{ menu.meal_time }}
                </span>
              </td>
              <td class="menu-text">{{ menu.menu_details }}</td>
              <td>
                <div class="action-buttons">
                  <button class="btn-icon edit" @click="openModal(menu)" title="Edit">✏️</button>
                  <button class="btn-icon delete" @click="deleteMenu(menu.id)" title="Hapus">🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editMode ? 'Edit Menu' : 'Tambah Menu Baru' }}</h2>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>
        
        <form @submit.prevent="saveMenu" class="modal-body">
          <div v-if="errorMsg" class="alert alert-error">{{ errorMsg }}</div>
          
          <div class="form-group">
            <label>Tanggal Disajikan</label>
            <input type="date" v-model="form.date_served" required :disabled="editMode" />
          </div>
          
          <div class="form-group">
            <label>Sesi Makan</label>
            <select v-model="form.meal_time" required :disabled="editMode">
              <option value="Pagi">Makan Pagi (Sarapan)</option>
              <option value="Siang">Makan Siang</option>
              <option value="Sore">Makan Sore</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Rincian Menu</label>
            <textarea v-model="form.menu_details" rows="4" placeholder="Nasi, Ayam Goreng, Sayur Sop..." required></textarea>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan Menu' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { request } from '@/api/auth';

const menus = ref([]);
const loading = ref(false);
const filterDate = ref('');

const showModal = ref(false);
const editMode = ref(false);
const saving = ref(false);
const errorMsg = ref('');

const form = ref({
  id: null,
  date_served: '',
  meal_time: 'Pagi',
  menu_details: ''
});

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
  });
}

async function fetchMenus() {
  loading.value = true;
  try {
    let url = '/kafetaria/menus';
    if (filterDate.value) {
      url += `?date=${filterDate.value}`;
    }
    const res = await request(url);
    menus.value = res.menus || [];
  } catch (err) {
    console.error('Failed to fetch menus', err);
  } finally {
    loading.value = false;
  }
}

function resetFilter() {
  filterDate.value = '';
  fetchMenus();
}

function openModal(menu = null) {
  if (menu) {
    editMode.value = true;
    form.value = { ...menu };
    // Potong timestamp dari datetime MySQL agar fit di input type="date"
    if (form.value.date_served.includes('T')) {
      form.value.date_served = form.value.date_served.split('T')[0];
    }
  } else {
    editMode.value = false;
    form.value = {
      id: null,
      date_served: new Date().toISOString().split('T')[0],
      meal_time: 'Pagi',
      menu_details: ''
    };
  }
  errorMsg.value = '';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveMenu() {
  saving.value = true;
  errorMsg.value = '';
  
  try {
    if (editMode.value) {
      await request(`/kafetaria/menus/${form.value.id}`, {
        method: 'PUT',
        body: JSON.stringify(form.value)
      });
    } else {
      await request('/kafetaria/menus', {
        method: 'POST',
        body: JSON.stringify(form.value)
      });
    }
    
    closeModal();
    fetchMenus();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Gagal menyimpan menu';
  } finally {
    saving.value = false;
  }
}

async function deleteMenu(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus menu ini?')) return;
  
  try {
    await request(`/kafetaria/menus/${id}`, { method: 'DELETE' });
    fetchMenus();
  } catch (err) {
    alert('Gagal menghapus menu');
  }
}

onMounted(() => {
  fetchMenus();
});
</script>

<style scoped>
.page-container {
  padding: 2rem 2.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0;
}

.content-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: flex-end;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  margin-bottom: 0.5rem;
}

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
  width: 100%;
}
input:focus, select:focus, textarea:focus {
  border-color: #f59e0b;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn-primary { background: #f59e0b; color: white; }
.btn-primary:hover { background: #d97706; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; }

.btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #475569; }
.btn-outline:hover { background: #f1f5f9; }

.menu-table {
  width: 100%;
  border-collapse: collapse;
}

.menu-table th {
  text-align: left;
  padding: 1rem;
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
  border-bottom: 2px solid #e2e8f0;
}

.menu-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
}
.badge.pagi { background: #e0f2fe; color: #0284c7; }
.badge.siang { background: #fef3c7; color: #d97706; }
.badge.sore { background: #ede9fe; color: #7c3aed; }

.menu-text {
  white-space: pre-wrap;
  color: #334155;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  opacity: 0.7;
  transition: opacity 0.2s;
}
.btn-icon:hover { opacity: 1; }

.text-center { text-align: center; }
.text-muted { color: #94a3b8; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #94a3b8;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.alert-error {
  background: #fef2f2;
  color: #ef4444;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #fecaca;
}
</style>
