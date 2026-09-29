<template>
  <div class="penghuni-kamar">
    <div class="page-header">
      <h2>Daftar Penghuni & Mutasi Kamar</h2>
      <p class="text-muted">Lihat semua siswa yang sudah menempati kamar dan lakukan mutasi jika perlu.</p>
    </div>

    <div class="table-container">
      <div class="table-actions">
        <input type="text" v-model="searchQuery" placeholder="Cari nama siswa atau kamar..." class="search-input" />
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Siswa</th>
            <th>No. Kamar</th>
            <th>Tanggal Disetujui</th>
            <th>Catatan</th>
            <th>Aksi (Mutasi)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-4">Memuat data penghuni...</td></tr>
          <tr v-else-if="filteredList.length === 0"><td colspan="5" class="text-center py-4 text-muted">Belum ada penghuni / tidak ada hasil pencarian.</td></tr>
          <tr v-else v-for="item in filteredList" :key="item.id">
            <td><strong>{{ item.user?.name || 'Siswa' }}</strong></td>
            <td>
              <span class="badge badge-primary">
                {{ item.kamar?.nomor_kamar || '-' }}
              </span>
            </td>
            <td class="text-sm">{{ item.approved_at ? new Date(item.approved_at).toLocaleDateString('id-ID') : '-' }}</td>
            <td class="text-sm text-muted max-w-xs">{{ item.notes || '-' }}</td>
            <td>
              <button class="btn-sm btn-mutasi" @click="openMutasiModal(item)">Mutasi / Pindah Kamar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Mutasi -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-content">
        <h3>Mutasi Kamar Siswa</h3>
        <p class="mb-4">Pindahkan siswa <strong>{{ selectedItem?.user?.name }}</strong> dari kamar <strong>{{ selectedItem?.kamar?.nomor_kamar }}</strong> ke kamar baru.</p>
        
        <form @submit.prevent="submitMutasi">
          <div class="form-group">
            <label>Pilih Kamar Tujuan</label>
            <select v-model="targetKamarId" required>
              <option value="" disabled>-- Pilih Kamar --</option>
              <option v-for="k in kamarTersedia" :key="k.id" :value="k.id" :disabled="k.current_occupancy >= k.kapasitas">
                {{ k.nomor_kamar }} (Terisi: {{ k.current_occupancy }}/{{ k.kapasitas }}) 
                <span v-if="k.current_occupancy >= k.kapasitas">- Penuh</span>
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Catatan Mutasi</label>
            <textarea v-model="mutasiNotes" rows="2" placeholder="Alasan mutasi..."></textarea>
          </div>
          
          <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
          
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn-primary" :disabled="saving || !targetKamarId">
              {{ saving ? 'Memproses...' : 'Proses Mutasi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const penghuniList = ref([]);
const kamarTersedia = ref([]);
const loading = ref(true);
const searchQuery = ref('');

// Mutasi Modal State
const showModal = ref(false);
const saving = ref(false);
const errorMsg = ref('');
const selectedItem = ref(null);
const targetKamarId = ref('');
const mutasiNotes = ref('');

const filteredList = computed(() => {
  if (!searchQuery.value) return penghuniList.value;
  const q = searchQuery.value.toLowerCase();
  return penghuniList.value.filter(item => {
    return (item.user?.name || '').toLowerCase().includes(q) || 
           (item.kamar?.nomor_kamar || '').toLowerCase().includes(q);
  });
});

onMounted(() => {
  fetchPenghuni();
  fetchKamarTersedia();
});

async function fetchPenghuni() {
  loading.value = true;
  try {
    const res = await fetch('/api/staff/asrama/penghuni', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    });
    if (res.ok) {
      const data = await res.json();
      penghuniList.value = data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

async function fetchKamarTersedia() {
  try {
    const res = await fetch('/api/staff/asrama/kamar', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    });
    if (res.ok) {
      const data = await res.json();
      kamarTersedia.value = data.data;
    }
  } catch (e) {
    console.error(e);
  }
}

function openMutasiModal(item) {
  selectedItem.value = item;
  targetKamarId.value = '';
  mutasiNotes.value = `Dimutasi dari kamar ${item.kamar?.nomor_kamar} oleh Staff Asrama.`;
  errorMsg.value = '';
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedItem.value = null;
}

async function submitMutasi() {
  saving.value = true;
  errorMsg.value = '';
  try {
    const res = await fetch('/api/staff/asrama/mutasi', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        selection_id: selectedItem.value.id,
        target_kamar_id: targetKamarId.value,
        notes: mutasiNotes.value
      })
    });
    
    const data = await res.json();
    if (res.ok) {
      alert('Mutasi berhasil dilakukan.');
      closeModal();
      fetchPenghuni();
      fetchKamarTersedia(); // Refresh occupancy
    } else {
      errorMsg.value = data.message || 'Gagal memproses mutasi.';
    }
  } catch (e) {
    errorMsg.value = 'Terjadi kesalahan jaringan.';
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.penghuni-kamar { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header h2 { margin: 0 0 0.5rem 0; color: #1e293b; font-size: 1.5rem; }
.text-muted { color: #64748b; margin: 0; }
.max-w-xs { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.table-container { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.table-actions { padding: 1rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.search-input { width: 100%; max-width: 350px; padding: 0.6rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; }

.data-table { width: 100%; border-collapse: collapse; text-align: left; }
.data-table th { background: #f8fafc; padding: 1rem; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }

.badge { padding: 0.35rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }
.badge-primary { background: #e0e7ff; color: #4338ca; }

.btn-sm { border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem; }
.btn-mutasi { background: #f59e0b; color: white; }
.btn-mutasi:hover { background: #d97706; }

/* Modal */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.modal-content h3 { margin: 0 0 0.5rem 0; color: #1e293b; }
.mb-4 { margin-bottom: 1.5rem; color: #475569; font-size: 0.95rem; line-height: 1.4; }

.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
.form-group select, .form-group textarea { padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; }
.modal-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
.error-msg { color: #ef4444; font-size: 0.85rem; margin-bottom: 1rem; }

.btn-primary { background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-primary:hover:not(:disabled) { background: #7c3aed; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #e2e8f0; color: #475569; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-secondary:hover { background: #cbd5e1; }
</style>
