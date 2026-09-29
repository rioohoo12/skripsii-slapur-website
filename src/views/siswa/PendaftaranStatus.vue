<template>
  <div class="status-page">
    <div class="status-layout">
      <!-- Konten utama -->
      <main class="status-main">
        <!-- Petunjuk Pendaftaran Berurutan -->
        <PageCard :jenis-kelamin="jenisKelamin" class="card-petunjuk">
          <template #header>Petunjuk & Aturan Pendaftaran Berurutan</template>
          <div class="petunjuk-body">
            <ol class="petunjuk-list">
              <li><strong>Alur Berurutan Wajib:</strong> Langkah 0 s/d Langkah 5 harus dilakukan secara berurutan.</li>
              <li><strong>Syarat Pembukaan Langkah:</strong> Langkah berikutnya hanya terbuka jika langkah sebelumnya telah <strong>terverifikasi (selesai)</strong>.</li>
              <li><strong>Status Terkunci:</strong> Apabila langkah sebelumnya belum terverifikasi, langkah berikutnya akan berstatus <strong>🔒 Terkunci</strong> dan tidak dapat diakses.</li>
              <li><strong>Hasil Akhir:</strong> Setelah menyelesaikan Langkah 5 (Upload Dokumen), jadwal pelajaran dan nomor makan (dining) Anda akan otomatis diaktifkan.</li>
            </ol>

            <div class="banner-info" :class="themeClass">
              <span class="banner-emoji">ℹ️</span>
              <span>Pastikan Anda menyelesaikan setiap langkah berurutan agar status terverifikasi.</span>
            </div>
          </div>
        </PageCard>

        <!-- Status Pendaftaran - Kartu Langkah Berurutan -->
        <h3 class="section-title">
          <span class="title-icon">📋</span>
          Status & Tahapan Pendaftaran (Langkah 0 – 5)
        </h3>

        <!-- Alert Modal/Banner jika klik langkah terkunci -->
        <Transition name="fade">
          <div v-if="lockedAlert" class="locked-alert-box">
            <span class="alert-icon">🔒</span>
            <div class="alert-text">
              <strong>Langkah Terkunci!</strong>
              <span>{{ lockedAlert }}</span>
            </div>
            <button class="close-alert" @click="lockedAlert = null">✕</button>
          </div>
        </Transition>

        <div class="status-cards">
          <div
            v-for="(step, i) in langkahPendaftaran"
            :key="i"
            class="status-card"
            :class="[
              themeClass, 
              { 
                completed: step.selesai, 
                locked: !step.unlocked, 
                active: step.unlocked && !step.selesai 
              }
            ]"
            @click="handleStepClick(step)"
          >
            <div class="status-card-step-num">{{ i }}</div>
            <div class="status-card-icon" :class="'icon-' + step.iconType">
              <span class="icon-emoji" aria-hidden="true">{{ step.unlocked ? step.icon : '🔒' }}</span>
            </div>
            
            <div class="status-card-body">
              <h4 class="status-card-title">
                Langkah {{ i }} - {{ step.judulFull }}
              </h4>
              <p class="status-card-desc">
                <span v-if="step.selesai" class="text-success">✔ {{ step.statusLabel }}</span>
                <span v-else-if="!step.unlocked" class="text-locked">🔒 {{ step.statusLabel }}</span>
                <span v-else class="text-pending">⏳ {{ step.statusLabel }}</span>
              </p>
            </div>

            <!-- Badges -->
            <div v-if="step.selesai" class="status-card-badge completed">
              <span class="badge-check">✓</span>
            </div>
            <div v-else-if="!step.unlocked" class="status-card-badge locked">
              <span class="badge-lock">🔒</span>
            </div>
            <div v-else class="status-card-pending">
              <span class="pending-dot"></span>
              <span class="pending-text">Proses</span>
            </div>

            <span v-if="step.unlocked" class="status-card-arrow">→</span>
          </div>
        </div>
      </main>

      <!-- Sidebar Kanan -->
      <aside class="status-sidebar">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-profile">
          <template #header>Mahasiswa / Siswa</template>
          <div class="profile-body">
            <div class="profile-avatar">{{ inisialNama }}</div>
            <p class="profile-nama">{{ namaSiswa }}</p>
            <p class="profile-id">ID: {{ nisAtauId }}</p>
            <p class="profile-prodi">{{ prodiAtauKelas }}</p>
          </div>
        </PageCard>

        <PageCard :jenis-kelamin="jenisKelamin" class="card-tahun">
          <template #header>Tahun Ajaran & Semester</template>
          <div class="tahun-body">
            <select v-model="tahunSemester" class="select-tahun">
              <option value="2025/2026 - GENAP">2025/2026 - GENAP</option>
              <option value="2025/2026 - GANJIL">2025/2026 - GANJIL</option>
              <option value="2024/2025 - GENAP">2024/2025 - GENAP</option>
            </select>
          </div>
        </PageCard>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const route = useRoute();
const router = useRouter();

const jk = computed(() => props.jenisKelamin || route.params.jenisKelamin || 'laki-laki');
const themeClass = computed(() => 'theme-' + jk.value);
const tahunSemester = ref('2025/2026 - GENAP');
const lockedAlert = ref(null);

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});
const namaSiswa = computed(() => user.value.name || user.value.nama || 'Siswa');
const inisialNama = computed(() => {
  const n = namaSiswa.value.trim();
  if (!n) return '?';
  const parts = n.split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return n.slice(0, 2).toUpperCase();
});
const nisAtauId = computed(() => user.value.id ?? user.value.nis ?? '-');
const prodiAtauKelas = computed(() => user.value.prodi ?? user.value.kelas ?? 'SLA Purwodadi');

const langkahPendaftaran = ref([
  {
    index: 0,
    judulFull: 'Permohonan Pendaftaran',
    icon: '📝',
    iconType: 'permohonan',
    path: 'pendaftaran/form',
    unlocked: true,
    selesai: false,
    statusLabel: 'Menunggu Pengisian Form',
  },
  {
    index: 1,
    judulFull: 'Clearance Slip (Pembayaran)',
    icon: '✅',
    iconType: 'clearance',
    path: 'clearance/pembayaran-pendaftaran',
    unlocked: false,
    selesai: false,
    statusLabel: 'Terkunci (Selesaikan Langkah 0)',
  },
  {
    index: 2,
    judulFull: 'Asrama / Luar Asrama (Pilih Kamar)',
    icon: '🏠',
    iconType: 'asrama',
    path: 'pendaftaran/kamar',
    unlocked: false,
    selesai: false,
    statusLabel: 'Terkunci (Selesaikan Langkah 1)',
  },
  {
    index: 3,
    judulFull: 'Administrasi',
    icon: '📁',
    iconType: 'administrasi',
    path: 'administrasi/surat',
    unlocked: false,
    selesai: false,
    statusLabel: 'Terkunci (Selesaikan Langkah 2)',
  },
  {
    index: 4,
    judulFull: 'Kurikulum',
    icon: '📚',
    iconType: 'kurikulum',
    path: 'pendaftaran/kurikulum',
    unlocked: false,
    selesai: false,
    statusLabel: 'Terkunci (Selesaikan Langkah 3)',
  },
  {
    index: 5,
    judulFull: 'Upload Dokumen',
    icon: '📎',
    iconType: 'dokumen',
    path: 'pendaftaran/dokumen',
    unlocked: false,
    selesai: false,
    statusLabel: 'Terkunci (Selesaikan Langkah 4)',
  },
]);

async function fetchStatus() {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    const res = await fetch('/api/pendaftaran/status', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      const data = await res.json();
      if (data.steps) {
        Object.keys(data.steps).forEach((idx) => {
          const apiStep = data.steps[idx];
          if (langkahPendaftaran.value[idx]) {
            langkahPendaftaran.value[idx].unlocked = apiStep.unlocked;
            langkahPendaftaran.value[idx].selesai = apiStep.selesai;
            langkahPendaftaran.value[idx].statusLabel = apiStep.status_label;
          }
        });
      }
    }
  } catch (e) {
    console.error('Failed to fetch pendaftaran status:', e);
  }
}

function handleStepClick(step) {
  if (!step.unlocked) {
    const prevIndex = step.index - 1;
    const prevStep = langkahPendaftaran.value[prevIndex];
    lockedAlert.value = `Langkah ${step.index} (${step.judulFull}) masih terkunci. Anda harus meverifikasi dan menyelesaikan Langkah ${prevIndex} (${prevStep?.judulFull}) terlebih dahulu.`;
    return;
  }
  if (step.index === 0 && step.selesai) {
    lockedAlert.value = `Anda sudah mengisi dan menyelesaikan ${step.judulFull}. Data tidak dapat diubah lagi.`;
    return;
  }
  lockedAlert.value = null;
  router.push(`/siswa/${jk.value}/${step.path}`);
}

onMounted(() => {
  fetchStatus();
});
</script>

<style scoped>
.status-page {
  padding-bottom: 2rem;
}

.status-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .status-layout {
    grid-template-columns: 1fr;
  }
}

.status-main {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-petunjuk :deep(.page-card-body) {
  padding: 1.5rem;
}

.petunjuk-list {
  margin: 0 0 1.25rem 0;
  padding-left: 1.35rem;
  color: #334155;
  line-height: 1.7;
  font-size: 0.95rem;
}

.petunjuk-list li {
  margin-bottom: 0.4rem;
}

.banner-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
  border: 1px dashed currentColor;
  opacity: 0.9;
}

.banner-info.theme-laki-laki {
  background: #f0fdfa;
  color: #0f766e;
}

.banner-info.theme-perempuan {
  background: #f5f3ff;
  color: #5b21b6;
}

.banner-emoji {
  font-size: 1.35rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.title-icon {
  font-size: 1.25rem;
}

/* Locked Alert Box */
.locked-alert-box {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 1rem 1.25rem;
  border-radius: 14px;
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  position: relative;
}

.alert-icon {
  font-size: 1.5rem;
}

.alert-text {
  display: flex;
  flex-direction: column;
  font-size: 0.9rem;
}

.alert-text strong {
  font-size: 0.95rem;
  margin-bottom: 0.2rem;
}

.close-alert {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: transparent;
  border: none;
  font-weight: bold;
  color: #991b1b;
  cursor: pointer;
}

.status-cards {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.status-card {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  padding: 1.35rem 1.5rem;
  padding-right: 2.75rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
  cursor: pointer;
}

.status-card.locked {
  background: #f8fafc;
  border-style: dashed;
  border-color: #cbd5e1;
  opacity: 0.75;
}

.status-card.locked:hover {
  border-color: #ef4444;
  background: #fff5f5;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
}

.status-card.active {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.status-card.completed {
  border-left: 5px solid #10b981;
  background: #ecfdf5;
  border-color: #a7f3d0;
}

.status-card-arrow {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.1rem;
  font-weight: 700;
  color: #94a3b8;
  transition: transform 0.2s ease;
}

.status-card:hover .status-card-arrow {
  transform: translateY(-50%) translateX(3px);
  color: #0f1e3c;
}

.status-card-step-num {
  position: absolute;
  top: 1rem;
  left: 1.25rem;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 0.85rem;
  font-weight: 700;
  color: #ffffff;
  background: #64748b;
}

.status-card.completed .status-card-step-num {
  background: #10b981;
}

.status-card.active .status-card-step-num {
  background: #3b82f6;
}

.status-card.locked .status-card-step-num {
  background: #94a3b8;
}

.status-card-icon {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  font-size: 1.6rem;
  margin-left: 2rem;
  background: #f1f5f9;
}

.status-card-body {
  flex: 1;
  min-width: 0;
}

.status-card-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.35rem 0;
}

.status-card-desc {
  font-size: 0.88rem;
  margin: 0;
  font-weight: 600;
}

.text-success { color: #047857; }
.text-pending { color: #1d4ed8; }
.text-locked { color: #991b1b; }

.status-card-badge {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 0.95rem;
  font-weight: 700;
}

.status-card-badge.completed {
  background: #10b981;
  color: #fff;
}

.status-card-badge.locked {
  background: #cbd5e1;
  color: #475569;
}

.status-card-pending {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #3b82f6;
}

.pending-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #3b82f6;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.15); }
}

.pending-text {
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

/* Sidebar */
.status-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-profile :deep(.page-card-body),
.card-tahun :deep(.page-card-body) {
  padding: 1.25rem;
}

.profile-body {
  text-align: center;
}

.profile-avatar {
  width: 64px;
  height: 64px;
  margin: 0 auto 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e2e8f0;
  border-radius: 50%;
  font-size: 1.35rem;
  font-weight: 700;
  color: #475569;
}

.profile-nama {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.profile-id,
.profile-prodi {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 0.15rem 0;
}

.select-tahun {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
