<template>
  <div class="asrama-page">
    <!-- Header Card -->
    <div class="header-card">
      <div class="header-info">
        <span class="badge-tag">FASILITAS ASRAMA SEKOLAH</span>
        <h2>Status & Informasi Kamar Asrama</h2>
        <p>Pantau nomor kamar asrama terdaftar dan status persetujuan penempatan / ganti kamar dari Staff Asrama.</p>
      </div>

      <div v-if="asramaData.has_selected_room" class="room-number-card">
        <div class="label">NOMOR KAMAR ANDA</div>
        <div class="number-display">{{ asramaData.nomor_kamar }}</div>
        <div class="sub-info">
          <span>Kapasitas: <strong>{{ asramaData.current_occupancy }}/{{ asramaData.kapasitas }} Orang</strong></span>
        </div>
      </div>
    </div>

    <!-- Empty State: Belum Pilih Kamar -->
    <div v-if="!loading && !asramaData.has_selected_room" class="empty-card">
      <div class="empty-icon">🏰</div>
      <h3>Anda Belum Mendaftarkan Kamar Asrama</h3>
      <p>Silakan lakukan pemilihan nomor kamar asrama terlebih dahulu pada menu pendaftaran kamar.</p>
      <router-link :to="`/siswa/${jenisKelamin}/pendaftaran/kamar`" class="btn-primary">
        👉 Pilih Kamar Asrama Sekarang
      </router-link>
    </div>

    <!-- Main Content when Room Selected -->
    <div v-else-if="asramaData.has_selected_room" class="asrama-content">
      <!-- Status Persetujuan Banner -->
      <div :class="['status-banner', getStatusClass(asramaData.status_persetujuan)]">
        <div class="status-icon">{{ getStatusIcon(asramaData.status_persetujuan) }}</div>
        <div class="status-details">
          <div class="status-title">{{ asramaData.status_label }}</div>
          <div class="status-desc">
            <span v-if="asramaData.requested_nomor_kamar && asramaData.status_persetujuan === 'pending'">
              📌 <strong>Pengajuan Ganti Kamar:</strong> Menunggu verifikasi Staff Asrama untuk pindah dari Kamar <strong>{{ asramaData.nomor_kamar }}</strong> ke Kamar <strong>{{ asramaData.requested_nomor_kamar }}</strong>.
            </span>
            <span v-else>{{ asramaData.notes || 'Status permohonan kamar aktif anda.' }}</span>
          </div>
          <div v-if="asramaData.approved_at" class="status-time">
            Waktu Verifikasi Terakhir: {{ asramaData.approved_at }}
          </div>
        </div>
      </div>

      <!-- Grid Cards -->
      <div class="info-grid">
        <!-- Card Details Kamar -->
        <div class="detail-card">
          <div class="card-title">
            <span>🚪 Detail Kamar Terdaftar</span>
          </div>
          <div class="detail-list">
            <div class="detail-item">
              <span class="label">Nomor Kamar:</span>
              <span class="value font-bold">Kamar {{ asramaData.nomor_kamar }}</span>
            </div>
            <div v-if="asramaData.requested_nomor_kamar" class="detail-item pending-change-item">
              <span class="label">Pengajuan Ganti:</span>
              <span class="value change-target">Kamar {{ asramaData.requested_nomor_kamar }} ⏳</span>
            </div>
            <div class="detail-item">
              <span class="label">Tipe Gedung:</span>
              <span class="value">Asrama {{ jenisKelamin === 'perempuan' ? 'Putri' : 'Putra' }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Kapasitas Maksimal:</span>
              <span class="value">{{ asramaData.kapasitas }} Orang / Kamar</span>
            </div>
            <div class="detail-item">
              <span class="label">Penghuni Terisi:</span>
              <span class="value highlight">{{ asramaData.current_occupancy }} Orang</span>
            </div>
          </div>
          <div class="card-footer">
            <button 
              @click="openModalGantiKamar" 
              class="btn-secondary" 
              :disabled="asramaData.status_persetujuan === 'pending'"
            >
              <span v-if="asramaData.status_persetujuan === 'pending'">⏳ Ganti Kamar Sedang Diproses Staff</span>
              <span v-else>🔄 Ajukan Ganti Kamar Asrama</span>
            </button>
          </div>
        </div>

        <!-- Card Daftar Teman Sekamar -->
        <div class="detail-card">
          <div class="card-title">
            <span>👥 Penghuni Kamar {{ asramaData.nomor_kamar }}</span>
          </div>
          <div class="roommates-list">
            <div 
              v-for="(person, idx) in asramaData.penghuni" 
              :key="idx"
              :class="['roommate-item', { is_me: person.is_current_user }]"
            >
              <div class="roommate-avatar">👤</div>
              <div class="roommate-info">
                <span class="roommate-name">{{ person.name }}</span>
                <span v-if="person.is_current_user" class="me-tag">Anda</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Ganti Kamar -->
    <div v-if="showModalGantiKamar" class="modal-backdrop" @click.self="showModalGantiKamar = false">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <h3>🔄 Permohonan Ganti Kamar Asrama</h3>
            <p class="modal-sub">Pilihlah kamar baru yang tersedia. Pengajuan ini akan diverifikasi oleh Staff Asrama.</p>
          </div>
          <button class="btn-close" @click="showModalGantiKamar = false">✕</button>
        </div>

        <div class="modal-body">
          <div v-if="changeErrorMsg" class="alert alert-error">{{ changeErrorMsg }}</div>
          <div v-if="changeSuccessMsg" class="alert alert-success">{{ changeSuccessMsg }}</div>

          <div class="current-room-info">
            <span>Kamar Saat Ini: <strong>Kamar {{ asramaData.nomor_kamar }}</strong></span>
          </div>

          <div class="form-group">
            <label class="form-label">Pilih Kamar Tujuan:</label>
            <div v-if="loadingRooms" class="loading-state">Mengambil daftar kamar...</div>
            <div v-else-if="availableRooms.length === 0" class="empty-state">Tidak ada kamar lain yang tersedia saat ini.</div>
            <div v-else class="rooms-grid">
              <div 
                v-for="room in availableRooms" 
                :key="room.id"
                :class="['room-option-card', { selected: selectedNewKamarId === room.id, disabled: room.id === asramaData.kamar_id || room.sisa_kuota <= 0 }]"
                @click="selectRoom(room)"
              >
                <div class="room-opt-header">
                  <span class="room-opt-name">Kamar {{ room.nomor_kamar }}</span>
                  <span :class="['kuota-badge', room.sisa_kuota > 0 ? 'available' : 'full']">
                    {{ room.sisa_kuota > 0 ? `Sisa: ${room.sisa_kuota} Kursi` : 'Penuh' }}
                  </span>
                </div>
                <div class="room-opt-detail">
                  <span>Gedung {{ room.gedung }} • Lantai {{ room.lantai }}</span>
                  <span class="occupancy">Kapasitas {{ room.occupied || 0 }}/{{ room.kapasitas }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="showModalGantiKamar = false">Batal</button>
          <button 
            class="btn-submit" 
            :disabled="!selectedNewKamarId || submittingChange"
            @click="submitGantiKamar"
          >
            {{ submittingChange ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Ganti Kamar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const loading = ref(true);
const showModalGantiKamar = ref(false);
const availableRooms = ref([]);
const loadingRooms = ref(false);
const selectedNewKamarId = ref(null);
const submittingChange = ref(false);
const changeSuccessMsg = ref('');
const changeErrorMsg = ref('');

const jenisKelamin = computed(() => {
  return authStore.user?.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
});

const asramaData = ref({
  has_selected_room: false,
  kamar_id: null,
  nomor_kamar: '',
  kapasitas: 4,
  current_occupancy: 0,
  requested_kamar_id: null,
  requested_nomor_kamar: null,
  status_persetujuan: 'approved',
  status_label: 'Disetujui oleh Staff Asrama',
  notes: '',
  approved_at: null,
  penghuni: [],
});

function getStatusClass(status) {
  if (status === 'approved') return 'approved';
  if (status === 'rejected') return 'rejected';
  return 'pending';
}

function getStatusIcon(status) {
  if (status === 'approved') return '✅';
  if (status === 'rejected') return '❌';
  return '⏳';
}

async function fetchAsramaInfo() {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    const res = await fetch('/api/asrama/info', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      asramaData.value = await res.json();
    }
  } catch (e) {
    console.error('Failed to fetch asrama info:', e);
  } finally {
    loading.value = false;
  }
}

async function openModalGantiKamar() {
  showModalGantiKamar.value = true;
  selectedNewKamarId.value = null;
  changeErrorMsg.value = '';
  changeSuccessMsg.value = '';
  loadingRooms.value = true;

  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/pendaftaran/kamar', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      const data = await res.json();
      availableRooms.value = data;
    }
  } catch (e) {
    console.error('Failed to fetch available rooms:', e);
  } finally {
    loadingRooms.value = false;
  }
}

function selectRoom(room) {
  if (room.id === asramaData.value.kamar_id || room.sisa_kuota <= 0) return;
  selectedNewKamarId.value = room.id;
}

async function submitGantiKamar() {
  if (!selectedNewKamarId.value) return;
  submittingChange.value = true;
  changeErrorMsg.value = '';
  changeSuccessMsg.value = '';

  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/asrama/ganti-kamar', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ kamar_id: selectedNewKamarId.value }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      changeSuccessMsg.value = data.message || 'Permohonan ganti kamar berhasil dikirim!';
      setTimeout(() => {
        showModalGantiKamar.value = false;
        fetchAsramaInfo();
      }, 1200);
    } else {
      changeErrorMsg.value = data.message || 'Gagal mengajukan ganti kamar.';
    }
  } catch (e) {
    changeErrorMsg.value = 'Terjadi kesalahan sistem.';
  } finally {
    submittingChange.value = false;
  }
}

onMounted(() => {
  fetchAsramaInfo();
});
</script>

<style scoped>
.asrama-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.header-card {
  background: linear-gradient(135deg, #0f1e3c 0%, #1e3a8a 100%);
  color: #ffffff;
  padding: 2.2rem;
  border-radius: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 25px rgba(15, 30, 60, 0.15);
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-info {
  max-width: 550px;
}

.badge-tag {
  background: rgba(255, 255, 255, 0.15);
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.header-info h2 {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0.6rem 0 0.4rem;
  color: #ffffff;
}

.header-info p {
  color: #93c5fd;
  font-size: 0.95rem;
  margin: 0;
}

.room-number-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem 2rem;
  border-radius: 16px;
  text-align: center;
  min-width: 220px;
}

.room-number-card .label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #bfdbfe;
  letter-spacing: 1px;
}

.number-display {
  font-size: 2.75rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0.2rem 0;
  letter-spacing: 2px;
}

.sub-info {
  font-size: 0.85rem;
  color: #e0f2fe;
}

.empty-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 3.5rem 2rem;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.empty-icon {
  font-size: 3.5rem;
  margin-bottom: 1rem;
}

.empty-card h3 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem;
}

.empty-card p {
  color: #64748b;
  max-width: 480px;
  margin: 0 auto 1.5rem;
}

.btn-primary {
  display: inline-block;
  background: #3b82f6;
  color: #ffffff;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.asrama-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Status Banner */
.status-banner {
  padding: 1.5rem;
  border-radius: 16px;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  border: 1px solid transparent;
}

.status-banner.approved {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #065f46;
}

.status-banner.pending {
  background: #fffbebfb;
  border-color: #fde68a;
  color: #92400e;
}

.status-banner.rejected {
  background: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

.status-icon {
  font-size: 2rem;
}

.status-title {
  font-size: 1.15rem;
  font-weight: 800;
  margin-bottom: 0.3rem;
}

.status-desc {
  font-size: 0.95rem;
  line-height: 1.4;
}

.status-time {
  font-size: 0.8rem;
  margin-top: 0.5rem;
  opacity: 0.85;
}

/* Grid Cards */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.5rem;
}

.detail-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 1.75rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 1.25rem;
}

.detail-list {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  flex: 1;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.95rem;
}

.detail-item .label {
  color: #64748b;
}

.detail-item .value {
  color: #1e293b;
  font-weight: 600;
}

.detail-item .value.highlight {
  color: #2563eb;
  font-weight: 700;
}

.card-footer {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.btn-secondary {
  display: inline-block;
  width: 100%;
  text-align: center;
  background: #f1f5f9;
  color: #334155;
  padding: 0.65rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  text-decoration: none;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #e2e8f0;
  color: #0f1e3c;
}

.roommates-list {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.roommate-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
}

.roommate-item.is_me {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.roommate-avatar {
  font-size: 1.25rem;
}

.roommate-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.roommate-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
}

.me-tag {
  background: #3b82f6;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
}

.pending-change-item {
  background: #fffbe6;
  padding: 0.4rem 0.6rem;
  border-radius: 8px;
  border: 1px dashed #ffe58f;
}

.change-target {
  color: #d46b08;
  font-weight: 700;
}

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 1rem;
}

.modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 580px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalFadeIn 0.25s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  padding: 1.5rem 1.75rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f1e3c;
  margin: 0;
}

.modal-sub {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0.25rem 0 0;
}

.btn-close {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 6px;
}

.btn-close:hover {
  color: #1e293b;
  background: #f1f5f9;
}

.modal-body {
  padding: 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  max-height: 60vh;
  overflow-y: auto;
}

.current-room-info {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-size: 0.9rem;
  color: #334155;
}

.form-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.6rem;
  display: block;
}

.rooms-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.room-option-card {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.9rem 1.1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #ffffff;
}

.room-option-card:hover:not(.disabled) {
  border-color: #93c5fd;
  background: #f0f7ff;
}

.room-option-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.room-option-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f8fafc;
}

.room-opt-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.room-opt-name {
  font-weight: 700;
  color: #0f1e3c;
  font-size: 1rem;
}

.kuota-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
}

.kuota-badge.available {
  background: #dcfce7;
  color: #166534;
}

.kuota-badge.full {
  background: #fee2e2;
  color: #991b1b;
}

.room-opt-detail {
  display: flex;
  justify-content: space-between;
  margin-top: 0.4rem;
  font-size: 0.825rem;
  color: #64748b;
}

.modal-footer {
  padding: 1.25rem 1.75rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  background: #f8fafc;
}

.btn-cancel {
  background: #e2e8f0;
  color: #475569;
  border: none;
  padding: 0.65rem 1.25rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #cbd5e1;
}

.btn-submit {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.65rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-submit:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-submit:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 600;
}

.alert-error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.alert-success {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}
</style>
