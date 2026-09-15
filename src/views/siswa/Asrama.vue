<template>
  <div class="asrama-page">
    <!-- Header Card -->
    <div class="header-card">
      <div class="header-info">
        <span class="badge-tag">FASILITAS ASRAMA SEKOALH</span>
        <h2>Status & Informasi Kamar Asrama</h2>
        <p>Pantau nomor kamar asrama terdaftar dan status persetujuan penempatan kamar dari Staff Asrama.</p>
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
          <div class="status-desc">{{ asramaData.notes }}</div>
          <div v-if="asramaData.approved_at" class="status-time">
            Waktu Verifikasi: {{ asramaData.approved_at }}
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
            <router-link :to="`/siswa/${jenisKelamin}/pendaftaran/kamar`" class="btn-secondary">
              🔄 Ganti Pilihan Kamar
            </router-link>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const loading = ref(true);

const jenisKelamin = computed(() => {
  return authStore.user?.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
});

const asramaData = ref({
  has_selected_room: false,
  nomor_kamar: '',
  kapasitas: 4,
  current_occupancy: 0,
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
</style>
