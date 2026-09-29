<template>
  <div class="manajemen-kamar">
    <div class="page-header">
      <h2>Manajemen Kamar</h2>
      <button class="btn-primary" @click="openModal()">+ Tambah Kamar Baru</button>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>No. Kamar</th>
            <th>Kapasitas</th>
            <th>Terisi</th>
            <th>Kondisi</th>
            <th>Fasilitas</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-4">Memuat data kamar...</td></tr>
          <tr v-else-if="kamarList.length === 0"><td colspan="6" class="text-center py-4 text-muted">Belum ada data kamar.</td></tr>
          <tr v-else v-for="kamar in kamarList" :key="kamar.id">
            <td><strong>{{ kamar.nomor_kamar }}</strong></td>
            <td>{{ kamar.kapasitas }}</td>
            <td>
              <span :class="{'text-danger font-bold': kamar.current_occupancy >= kamar.kapasitas}">
                {{ kamar.current_occupancy }} / {{ kamar.kapasitas }}
              </span>
            </td>
            <td>
              <span :class="['badge', getKondisiBadge(kamar.status_kondisi)]">
                {{ kamar.status_kondisi || 'Baik' }}
              </span>
            </td>
            <td class="text-sm text-muted">{{ kamar.catatan_fasilitas || '-' }}</td>
            <td>
              <div class="action-btns">
                <button class="btn-icon text-blue" @click="openModal(kamar)" title="Edit">✏️</button>
                <button class="btn-icon text-red" @click="deleteKamar(kamar.id)" title="Hapus">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form Kamar -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-content">
        <h3>{{ isEdit ? 'Edit Kamar' : 'Tambah Kamar Baru' }}</h3>
        <form @submit.prevent="saveKamar">
          <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" v-model="form.nomor_kamar" required placeholder="Contoh: A101" />
          </div>
          <div class="form-group">
            <label>Kapasitas</label>
            <input type="number" v-model="form.kapasitas" required min="1" />
          </div>
          <div class="form-group">
            <label>Status Kondisi</label>
            <select v-model="form.status_kondisi">
              <option value="Baik">Baik</option>
              <option value="Perbaikan">Perbaikan</option>
              <option value="Rusak">Rusak</option>
            </select>
          </div>
          <div class="form-group">
            <label>Catatan Fasilitas</label>
            <textarea v-model="form.catatan_fasilitas" rows="3" placeholder="Contoh: AC dingin, ranjang perlu perbaikan..."></textarea>
          </div>
          <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const kamarList = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const isEdit = ref(false);
const errorMsg = ref('');

const form = ref({
  id: null,
  nomor_kamar: '',
  kapasitas: 4,
  status_kondisi: 'Baik',
  catatan_fasilitas: ''
});

onMounted(() => {
  fetchKamar();
});

async function fetchKamar() {
  loading.value = true;
  try {
    const res = await fetch('/api/staff/asrama/kamar', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    });
    if (res.ok) {
      const data = await res.json();
      kamarList.value = data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

function openModal(kamar = null) {
  errorMsg.value = '';
  if (kamar) {
    isEdit.value = true;
    form.value = { ...kamar };
  } else {
    isEdit.value = false;
    form.value = {
      id: null, nomor_kamar: '', kapasitas: 4, status_kondisi: 'Baik', catatan_fasilitas: ''
    };
  }
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveKamar() {
  saving.value = true;
  errorMsg.value = '';
  try {
    const url = isEdit.value ? `/api/staff/asrama/kamar/${form.value.id}` : '/api/staff/asrama/kamar';
    const method = isEdit.value ? 'PUT' : 'POST';
    
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify(form.value)
    });
    
    const data = await res.json();
    if (res.ok) {
      closeModal();
      fetchKamar();
    } else {
      errorMsg.value = data.message || 'Gagal menyimpan kamar.';
    }
  } catch (e) {
    errorMsg.value = 'Terjadi kesalahan jaringan.';
  } finally {
    saving.value = false;
  }
}

async function deleteKamar(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus kamar ini?')) return;
  try {
    const res = await fetch(`/api/staff/asrama/kamar/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    });
    if (res.ok) {
      fetchKamar();
    } else {
      const data = await res.json();
      alert(data.message || 'Gagal menghapus kamar.');
    }
  } catch (e) {
    alert('Terjadi kesalahan jaringan.');
  }
}

function getKondisiBadge(kondisi) {
  if (kondisi === 'Baik') return 'badge-success';
  if (kondisi === 'Perbaikan') return 'badge-warning';
  if (kondisi === 'Rusak') return 'badge-danger';
  return 'badge-secondary';
}
</script>

<style scoped>
.manajemen-kamar { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
.page-header h2 { margin: 0; color: #1e293b; font-size: 1.5rem; }
.btn-primary { background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background: #7c3aed; }
.btn-secondary { background: #e2e8f0; color: #475569; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-secondary:hover { background: #cbd5e1; }

.table-container { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; }
.data-table th { background: #f8fafc; padding: 1rem; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
.data-table tr:last-child td { border-bottom: none; }

.badge { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef08a; color: #854d0e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-secondary { background: #f1f5f9; color: #475569; }

.action-btns { display: flex; gap: 0.5rem; }
.btn-icon { background: none; border: none; font-size: 1.1rem; cursor: pointer; padding: 0.2rem; border-radius: 4px; }
.btn-icon:hover { background: #f1f5f9; }

.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.modal-content h3 { margin: 0 0 1.5rem 0; color: #1e293b; }

.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
.form-group input, .form-group select, .form-group textarea { padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
.error-msg { color: #ef4444; font-size: 0.85rem; margin-bottom: 1rem; }
</style>
