<template>
  <div class="asrama-page" :class="themeClass">
    <!-- Header Card -->
    <div class="header-card" :class="isPutri ? 'header-putri' : 'header-putra'">
      <div class="header-info">
        <span class="badge-tag">
          {{ isPutri ? '🌸 FASILITAS ASRAMA PUTRI SEKOLAH' : '🏰 FASILITAS ASRAMA PUTRA SEKOLAH' }}
        </span>
        <h2>Status & Informasi Kamar {{ asramaTitle }}</h2>
        <p>
          Pantau nomor kamar terdaftar, rincian gedung, dan permohonan kamar {{ asramaTitle }} SLAPUR.
        </p>
      </div>

      <!-- Room Number Badge if room belongs to this Dormitory -->
      <div v-if="hasRoomInThisDorm" class="room-number-card">
        <div class="label">NOMOR KAMAR ANDA</div>
        <div class="number-display">{{ asramaData.nomor_kamar }}</div>
        <div class="sub-info">
          <span>Kapasitas: <strong>{{ asramaData.current_occupancy }}/{{ asramaData.kapasitas }} Orang</strong></span>
        </div>
      </div>
    </div>

    <!-- Empty State: Belum Pilih Kamar di Asrama ini -->
    <div v-if="!loading && !hasRoomInThisDorm" class="empty-card">
      <div class="empty-icon">{{ isPutri ? '🏰' : '🏰' }}</div>
      <h3>Anda Belum Mendaftarkan Kamar {{ asramaTitle }}</h3>
      <p>
        Silakan lakukan pemilihan nomor kamar {{ asramaTitle }} yang tersedia untuk menyelesaikan proses registrasi asrama.
      </p>
      <router-link :to="`/siswa/${currentGender}/pendaftaran/kamar`" class="btn-primary" :class="isPutri ? 'btn-putri' : 'btn-putra'">
        👉 Pilih Kamar {{ asramaTitle }} Sekarang
      </router-link>
    </div>

    <!-- Main Content when Room Selected in This Dorm -->
    <div v-else-if="hasRoomInThisDorm" class="asrama-content">
      <!-- Status Persetujuan Banner -->
      <div :class="['status-banner', getStatusClass(asramaData.status_persetujuan)]">
        <div class="status-icon">{{ getStatusIcon(asramaData.status_persetujuan) }}</div>
        <div class="status-details">
          <div class="status-title">{{ asramaData.status_label }}</div>
          <div class="status-desc">
            <span v-if="asramaData.requested_nomor_kamar && asramaData.status_persetujuan === 'pending'">
              📌 <strong>Pengajuan Ganti Kamar:</strong> Menunggu verifikasi Staff Asrama untuk pindah dari Kamar <strong>{{ asramaData.nomor_kamar }}</strong> ke Kamar <strong>{{ asramaData.requested_nomor_kamar }}</strong>.
            </span>
            <span v-else>{{ asramaData.notes || 'Status permohonan kamar aktif Anda di ' + asramaTitle + '.' }}</span>
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
              <span class="value">{{ asramaTitle }} (Gedung {{ getGedungName(asramaData.nomor_kamar) }})</span>
            </div>
            <div class="detail-item">
              <span class="label">Lantai:</span>
              <span class="value">Lantai {{ getLantaiName(asramaData.nomor_kamar) }}</span>
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
              <span v-else>🔄 Ajukan Ganti Kamar {{ asramaTitle }}</span>
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
            <div v-if="!asramaData.penghuni || asramaData.penghuni.length === 0" class="empty-roommates">
              Belum ada data penghuni lain di kamar ini.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Gedung & Kamar Asrama (Identical layout for both Putra and Putri) -->
    <div class="buildings-section">
      <div class="section-header">
        <div>
          <h3>🏢 Daftar Gedung {{ asramaTitle }}</h3>
          <p class="section-sub">Struktur gedung dan alokasi kamar khusus santri {{ isPutri ? 'putri' : 'putra' }}.</p>
        </div>
        <span class="badge-pill">{{ gedungList.length }} Gedung Aktif</span>
      </div>

      <div class="buildings-grid">
        <div 
          v-for="gedung in gedungList" 
          :key="gedung.kode" 
          class="building-card"
          :class="isPutri ? 'building-putri' : 'building-putra'"
        >
          <div class="building-card-header">
            <div class="building-icon-wrap">{{ gedung.icon }}</div>
            <div>
              <h4 class="building-name">{{ gedung.nama }}</h4>
              <span class="building-floor">{{ gedung.lantai }}</span>
            </div>
          </div>
          <p class="building-desc">{{ gedung.deskripsi }}</p>
          <div class="building-rooms-list">
            <span class="rooms-label">Daftar Kamar:</span>
            <div class="rooms-tags">
              <span 
                v-for="kmr in gedung.kamar" 
                :key="kmr" 
                class="room-tag"
                :class="{ active_room: asramaData.nomor_kamar === kmr }"
              >
                Kamar {{ kmr }}
                <small v-if="asramaData.nomor_kamar === kmr"> (Kamar Anda)</small>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Fasilitas Asrama (Identical layout for both Putra and Putri) -->
    <div class="facilities-section">
      <div class="section-header">
        <div>
          <h3>✨ Fasilitas Unggulan {{ asramaTitle }}</h3>
          <p class="section-sub">Kenyamanan dan fasilitas standar asrama terpadu SLAPUR.</p>
        </div>
      </div>

      <div class="facilities-grid">
        <div 
          v-for="(f, i) in fasilitasAsrama" 
          :key="i" 
          class="facility-card"
        >
          <div class="facility-icon">{{ f.icon }}</div>
          <div class="facility-body">
            <div class="facility-name">{{ f.nama }}</div>
            <div class="facility-desc">{{ f.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Ganti Kamar -->
    <div v-if="showModalGantiKamar" class="modal-backdrop" @click.self="showModalGantiKamar = false">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <h3>🔄 Permohonan Ganti Kamar {{ asramaTitle }}</h3>
            <p class="modal-sub">Pilihlah kamar baru di {{ asramaTitle }} yang tersedia. Pengajuan ini akan diverifikasi oleh Staff Asrama.</p>
          </div>
          <button class="btn-close" @click="showModalGantiKamar = false">✕</button>
        </div>

        <div class="modal-body">
          <div v-if="changeErrorMsg" class="alert alert-error">{{ changeErrorMsg }}</div>
          <div v-if="changeSuccessMsg" class="alert alert-success">{{ changeSuccessMsg }}</div>

          <div class="current-room-info">
            <span>Kamar Saat Ini: <strong>Kamar {{ asramaData.nomor_kamar }}</strong> ({{ asramaTitle }})</span>
          </div>

          <div class="form-group">
            <label class="form-label">Pilih Kamar Tujuan ({{ asramaTitle }}):</label>
            <div v-if="loadingRooms" class="loading-state">Mengambil daftar kamar {{ asramaTitle }}...</div>
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
                  <span class="occupancy">Kapasitas {{ room.current_occupancy || 0 }}/{{ room.kapasitas }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancel" @click="showModalGantiKamar = false">Batal</button>
          <button 
            class="btn-submit" 
            :class="isPutri ? 'btn-submit-putri' : 'btn-submit-putra'"
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
  jenisKelamin: { type: String, default: '' },
  asramaType: { type: String, default: '' },
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(true);
const showModalGantiKamar = ref(false);
const availableRooms = ref([]);
const loadingRooms = ref(false);
const selectedNewKamarId = ref(null);
const submittingChange = ref(false);
const changeSuccessMsg = ref('');
const changeErrorMsg = ref('');

// Determine whether currently in Putri or Putra mode
const isPutri = computed(() => {
  if (props.jenisKelamin === 'perempuan') return true;
  if (props.jenisKelamin === 'laki-laki') return false;
  if (route.params.jenisKelamin === 'perempuan') return true;
  if (route.params.jenisKelamin === 'laki-laki') return false;
  return authStore.user?.jenis_kelamin === 'perempuan';
});

const currentGender = computed(() => isPutri.value ? 'perempuan' : 'laki-laki');
const asramaTitle = computed(() => isPutri.value ? 'Asrama Putri' : 'Asrama Putra');
const themeClass = computed(() => isPutri.value ? 'theme-putri' : 'theme-putra');

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

// Check if user's selected room belongs to this dorm (Putra rooms A, B, C vs Putri rooms D, E, F)
const hasRoomInThisDorm = computed(() => {
  if (!asramaData.value.has_selected_room || !asramaData.value.nomor_kamar) return false;
  const prefix = asramaData.value.nomor_kamar.charAt(0).toUpperCase();
  if (isPutri.value) {
    return ['D', 'E', 'F'].includes(prefix);
  } else {
    return ['A', 'B', 'C'].includes(prefix);
  }
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
    const genderQuery = isPutri.value ? 'perempuan' : 'laki-laki';
    const res = await fetch(`/api/pendaftaran/kamar?jenis_kelamin=${genderQuery}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      const data = await res.json();
      availableRooms.value = (data.kamar || []).map(k => ({
        ...k,
        sisa_kuota: k.kapasitas - k.current_occupancy,
        gedung: getGedungName(k.nomor_kamar),
        lantai: getLantaiName(k.nomor_kamar)
      }));
    }
  } catch (e) {
    console.error('Failed to fetch available rooms:', e);
  } finally {
    loadingRooms.value = false;
  }
}

function getGedungName(nomor_kamar) {
  if (!nomor_kamar) return 'Asrama';
  const prefix = nomor_kamar.charAt(0).toUpperCase();
  if (isPutri.value) {
    if (prefix === 'D') return 'Krisan';
    if (prefix === 'E') return 'Melati';
    if (prefix === 'F') return 'Mawar';
  } else {
    if (prefix === 'A') return 'Anex';
    if (prefix === 'B') return 'Cendrawasih';
    if (prefix === 'C') return 'Hawk';
  }
  return 'Asrama ' + prefix;
}

function getLantaiName(nomor_kamar) {
  if (!nomor_kamar || nomor_kamar.length < 2) return 1;
  const num = parseInt(nomor_kamar.substring(1), 10);
  if (isNaN(num)) return 1;
  if (num <= 3) return 1;
  if (num <= 6) return 2;
  return 3;
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

const gedungList = computed(() => {
  if (isPutri.value) {
    return [
      {
        kode: 'D',
        nama: 'Gedung Krisan',
        icon: '🌸',
        lantai: 'Lantai 1 - 3',
        kamar: ['D1', 'D2', 'D3'],
        deskripsi: 'Gedung utama asrama putri dengan suasana asri, tenang, dan ruang belajar kondusif.',
      },
      {
        kode: 'E',
        nama: 'Gedung Melati',
        icon: '🌼',
        lantai: 'Lantai 1 - 3',
        kamar: ['E1', 'E2', 'E3'],
        deskripsi: 'Gedung asrama putri sayap timur dekat dengan taman santri dan fasilitas rekreasi.',
      },
      {
        kode: 'F',
        nama: 'Gedung Mawar',
        icon: '🌹',
        lantai: 'Lantai 1 - 3',
        kamar: ['F1', 'F2', 'F3'],
        deskripsi: 'Gedung asrama putri modern dengan kamar mandi higienis dan ventilasi alami yang sejuk.',
      },
    ];
  } else {
    return [
      {
        kode: 'A',
        nama: 'Gedung Anex',
        icon: '🏢',
        lantai: 'Lantai 1 - 3',
        kamar: ['A1', 'A2', 'A3'],
        deskripsi: 'Gedung utama asrama putra dengan kapasitas luas dan fasilitas belajar santri terintegrasi.',
      },
      {
        kode: 'B',
        nama: 'Gedung Cendrawasih',
        icon: '🐦',
        lantai: 'Lantai 1 - 3',
        kamar: ['B1', 'B2', 'B3'],
        deskripsi: 'Gedung asrama putra sayap timur dengan akses langsung ke lapangan olahraga dan kantin.',
      },
      {
        kode: 'C',
        nama: 'Gedung Hawk',
        icon: '🦅',
        lantai: 'Lantai 1 - 3',
        kamar: ['C1', 'C2', 'C3'],
        deskripsi: 'Gedung asrama putra modern yang tenang untuk mendukung konsentrasi belajar santri.',
      },
    ];
  }
});

const fasilitasAsrama = computed(() => {
  return [
    { icon: '🛏️', nama: 'Springbed & Kasur Nyaman', desc: 'Kasur empuk lengkap dengan sprei & bantal standar asrama' },
    { icon: '🚪', nama: 'Lemari Pribadi', desc: 'Lemari pakaian 2 pintu dengan kunci pengaman per santri' },
    { icon: '📚', nama: 'Meja Belajar Ergonomis', desc: 'Meja & kursi belajar individual untuk fokus mandiri' },
    { icon: '🚿', nama: 'Kamar Mandi Bersih', desc: 'Sanitasi higienis dengan pasokan air lancar 24 jam' },
    { icon: '📶', nama: 'Wi-Fi Cepat 24 Jam', desc: 'Akses internet stabil untuk materi & riset akademik' },
    { icon: '🛡️', nama: isPutri.value ? 'Bunda Asrama & Resepsionis 24/7' : 'Pembina Asrama Putra 24/7', desc: 'Pengawasan keamanan, absensi malam, dan bimbingan karakter' },
  ];
});

watch(() => route.path, () => {
  fetchAsramaInfo();
});

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

/* Header Cards */
.header-card {
  color: #ffffff;
  padding: 2.2rem;
  border-radius: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 25px rgba(15, 30, 60, 0.12);
  flex-wrap: wrap;
  gap: 1.5rem;
  transition: background 0.3s ease;
}

.header-card.header-putra {
  background: linear-gradient(135deg, #0f1e3c 0%, #1e3a8a 100%);
}

.header-card.header-putri {
  background: linear-gradient(135deg, #3b0764 0%, #701a75 50%, #831843 100%);
}

.header-info {
  max-width: 550px;
}

.badge-tag {
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.header-info h2 {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0.6rem 0 0.4rem;
  color: #ffffff;
}

.header-info p {
  color: #bfdbfe;
  font-size: 0.95rem;
  margin: 0;
}

.header-putri .header-info p {
  color: #fbcfe8;
}

.room-number-card {
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.25);
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

.header-putri .room-number-card .label {
  color: #fbcfe8;
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

.header-putri .sub-info {
  color: #fce7f3;
}

/* Empty State Card */
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
  color: #ffffff;
  padding: 0.75rem 1.75rem;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-primary.btn-putra {
  background: #2563eb;
}

.btn-primary.btn-putra:hover {
  background: #1d4ed8;
}

.btn-primary.btn-putri {
  background: #db2777;
}

.btn-primary.btn-putri:hover {
  background: #be185d;
}

/* Asrama Content */
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
  background: #fffbeb;
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

.theme-putri .detail-item .value.highlight {
  color: #db2777;
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
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #e2e8f0;
  color: #0f1e3c;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

.theme-putri .roommate-item.is_me {
  background: #fdf2f8;
  border-color: #fbcfe8;
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

.theme-putri .me-tag {
  background: #db2777;
}

.empty-roommates {
  font-size: 0.85rem;
  color: #94a3b8;
  text-align: center;
  padding: 1.5rem 0;
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

/* Buildings Section */
.buildings-section {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 1.75rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-header h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f1e3c;
  margin: 0;
}

.section-sub {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0.25rem 0 0;
}

.badge-pill {
  background: #f1f5f9;
  color: #334155;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.buildings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}

.building-card {
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.4rem;
  transition: all 0.2s ease;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
}

.building-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.building-putra:hover {
  border-color: #93c5fd;
}

.building-putri:hover {
  border-color: #fbcfe8;
}

.building-card-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 0.85rem;
}

.building-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: #ffffff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.building-name {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f1e3c;
  margin: 0;
}

.building-floor {
  font-size: 0.75rem;
  color: #64748b;
}

.building-desc {
  font-size: 0.85rem;
  color: #475569;
  line-height: 1.4;
  margin: 0 0 1rem;
  flex: 1;
}

.building-rooms-list {
  padding-top: 0.85rem;
  border-top: 1px solid #e2e8f0;
}

.rooms-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 0.4rem;
  display: block;
}

.rooms-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.room-tag {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}

.room-tag.active_room {
  background: #dbeafe;
  border-color: #3b82f6;
  color: #1d4ed8;
  font-weight: 700;
}

.theme-putri .room-tag.active_room {
  background: #fce7f3;
  border-color: #ec4899;
  color: #be185d;
}

/* Facilities Section */
.facilities-section {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 1.75rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.facilities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
}

.facility-card {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.1rem;
  background: #f8fafc;
  border-radius: 14px;
  border: 1px solid #f1f5f9;
}

.facility-icon {
  font-size: 1.75rem;
  line-height: 1;
}

.facility-body {
  display: flex;
  flex-direction: column;
}

.facility-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f1e3c;
  margin-bottom: 0.25rem;
}

.facility-desc {
  font-size: 0.8rem;
  color: #64748b;
  line-height: 1.35;
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

.theme-putri .room-option-card:hover:not(.disabled) {
  border-color: #fbcfe8;
  background: #fdf2f8;
}

.room-option-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.theme-putri .room-option-card.selected {
  border-color: #db2777;
  background: #fdf2f8;
  box-shadow: 0 0 0 2px rgba(219, 39, 119, 0.2);
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
  color: white;
  border: none;
  padding: 0.65rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-submit.btn-submit-putra {
  background: #2563eb;
}

.btn-submit.btn-submit-putra:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-submit.btn-submit-putri {
  background: #db2777;
}

.btn-submit.btn-submit-putri:hover:not(:disabled) {
  background: #be185d;
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
