<template>
  <div class="persetujuan-kamar">
    <div class="page-header">
      <h2>Persetujuan Pendaftaran & Mutasi Kamar</h2>
      <p class="text-muted">Kelola permintaan pendaftaran baru atau pergantian kamar dari siswa.</p>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Tanggal Pengajuan</th>
            <th>Nama Siswa</th>
            <th>Kamar Saat Ini</th>
            <th>Kamar Diminta</th>
            <th>Catatan Siswa</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-4">Memuat data pengajuan...</td></tr>
          <tr v-else-if="pengajuanList.length === 0"><td colspan="6" class="text-center py-4 text-muted">Belum ada pengajuan kamar baru.</td></tr>
          <tr v-else v-for="item in pengajuanList" :key="item.id">
            <td>{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
            <td><strong>{{ item.user?.name || 'Siswa' }}</strong></td>
            <td>
              <span class="badge badge-secondary">
                {{ item.kamar ? item.kamar.nomor_kamar : 'Belum Ada' }}
              </span>
            </td>
            <td>
              <span class="badge badge-primary">
                {{ item.requested_kamar ? item.requested_kamar.nomor_kamar : (item.kamar ? item.kamar.nomor_kamar : '-') }}
              </span>
            </td>
            <td class="text-sm text-muted max-w-xs">{{ item.notes || '-' }}</td>
            <td>
              <div class="action-btns">
                <button class="btn-sm btn-success" @click="prosesPengajuan(item.id, 'approve')" :disabled="processing === item.id">Setujui</button>
                <button class="btn-sm btn-danger" @click="prosesPengajuan(item.id, 'reject')" :disabled="processing === item.id">Tolak</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const pengajuanList = ref([]);
const loading = ref(true);
const processing = ref(null);

onMounted(() => {
  fetchPengajuan();
});

async function fetchPengajuan() {
  loading.value = true;
  try {
    const res = await fetch('/api/staff/asrama/persetujuan', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    });
    if (res.ok) {
      const data = await res.json();
      pengajuanList.value = data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

async function prosesPengajuan(id, action) {
  if (!confirm(`Apakah Anda yakin ingin ${action === 'approve' ? 'MENYETUJUI' : 'MENOLAK'} pengajuan ini?`)) return;
  
  processing.value = id;
  try {
    const res = await fetch('/api/staff/asrama/approve', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        selection_id: id,
        action: action
      })
    });
    
    const data = await res.json();
    if (res.ok) {
      alert('Pengajuan berhasil diproses.');
      fetchPengajuan();
    } else {
      alert(data.message || 'Gagal memproses pengajuan.');
    }
  } catch (e) {
    alert('Terjadi kesalahan jaringan.');
  } finally {
    processing.value = null;
  }
}
</script>

<style scoped>
.persetujuan-kamar { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header h2 { margin: 0 0 0.5rem 0; color: #1e293b; font-size: 1.5rem; }
.text-muted { color: #64748b; margin: 0; }
.max-w-xs { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.table-container { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; }
.data-table th { background: #f8fafc; padding: 1rem; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }

.badge { padding: 0.35rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.badge-primary { background: #e0e7ff; color: #4338ca; }

.action-btns { display: flex; gap: 0.5rem; }
.btn-sm { border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem; }
.btn-sm:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-success { background: #10b981; color: white; }
.btn-success:hover:not(:disabled) { background: #059669; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover:not(:disabled) { background: #dc2626; }
</style>
