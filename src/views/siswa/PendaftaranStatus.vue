<template>
  <div class="status-page">
    <div class="status-layout">
      <!-- Konten utama -->
      <main class="status-main">
        <!-- Petunjuk Pendaftaran -->
        <PageCard :jenis-kelamin="jenisKelamin" class="card-petunjuk">
          <template #header>Petunjuk Pendaftaran</template>
          <div class="petunjuk-body">
            <ol class="petunjuk-list">
              <li>Langkah 0–2 dapat dilakukan pada periode yang ditentukan.</li>
              <li>Langkah 3 dilakukan sesuai jadwal resmi pendaftaran.</li>
              <li>Setelah menyelesaikan langkah 3, perubahan status tidak dapat dilakukan.</li>
              <li>Siswa yang tidak menyelesaikan langkah pendaftaran akan mendapat hold.</li>
              <li>Semua fitur perubahan hanya terbuka pada periode pendaftaran.</li>
            </ol>

            <div class="banner-info" :class="themeClass">
              <span class="banner-emoji">ℹ️</span>
              <span>Status pendaftaran Anda akan tampil di bawah setelah data tersedia.</span>
            </div>
          </div>
        </PageCard>

        <!-- Status Pendaftaran - Kartu langkah (gambaran, data kosong) -->
        <h3 class="section-title">
          <span class="title-icon">📋</span>
          Status Pendaftaran
        </h3>
        <div class="status-cards">
          <router-link
            v-for="(step, i) in langkahPendaftaran"
            :key="i"
            :to="stepRoute(step)"
            class="status-card"
            :class="[themeClass, { completed: step.selesai }]"
          >
            <div class="status-card-step-num">{{ i + 1 }}</div>
            <div class="status-card-icon" :class="'icon-' + step.iconType">
              <span class="icon-emoji" aria-hidden="true">{{ step.icon }}</span>
            </div>
            <div class="status-card-body">
              <h4 class="status-card-title">{{ step.judul }}</h4>
              <p class="status-card-desc">
                {{ step.selesai ? step.deskripsi : 'Belum ada data.' }}
              </p>
            </div>
            <div v-if="step.selesai" class="status-card-badge">
              <span class="badge-check">✓</span>
            </div>
            <div v-else class="status-card-pending">
              <span class="pending-dot"></span>
              <span class="pending-text">Menunggu</span>
            </div>
            <span class="status-card-arrow">→</span>
          </router-link>
        </div>
      </main>

      <!-- Sidebar kanan -->
      <aside class="status-sidebar">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-profile">
          <template #header>Mahasiswa</template>
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
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const route = useRoute();
const jk = computed(() => props.jenisKelamin || route.params.jenisKelamin || 'laki-laki');
const themeClass = computed(() => 'theme-' + jk.value);

function stepRoute(step) {
  return { path: `/siswa/${jk.value}/${step.path}` };
}

const tahunSemester = ref('2025/2026 - GENAP');

// Data user dari localStorage (gambaran)
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

// Langkah pendaftaran – gambaran kosong; path mengarah ke menu masing-masing
const langkahPendaftaran = ref([
  {
    judul: 'Langkah 0 - Permohonan Pendaftaran',
    deskripsi: '',
    icon: '📝',
    iconType: 'permohonan',
    path: 'pendaftaran/permohonan',
    selesai: false,
  },
  {
    judul: 'Langkah 1 - Clearance Slip',
    deskripsi: '',
    icon: '✅',
    iconType: 'clearance',
    path: 'clearance/mid',
    selesai: false,
  },
  {
    judul: 'Langkah 2 - Asrama / Luar Asrama',
    deskripsi: '',
    icon: '🏠',
    iconType: 'asrama',
    path: 'pendaftaran/kamar',
    selesai: false,
  },
  {
    judul: 'Langkah 3 - Administrasi',
    deskripsi: '',
    icon: '📁',
    iconType: 'administrasi',
    path: 'administrasi/surat',
    selesai: false,
  },
  {
    judul: 'Langkah 4 - Kurikulum',
    deskripsi: '',
    icon: '📚',
    iconType: 'kurikulum',
    // Kurikulum bukan dipilih siswa; ditampilkan otomatis sesuai kelas yang didaftar.
    path: 'pendaftaran/kurikulum',
    selesai: false,
  },
  {
    judul: 'Langkah 5 - Upload Dokumen',
    deskripsi: '',
    icon: '📎',
    iconType: 'dokumen',
    // Langsung ke upload dokumen (bukan menu Administrasi).
    path: 'pendaftaran/dokumen',
    selesai: false,
  },
]);
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
  transition: box-shadow 0.25s ease, border-color 0.2s ease, transform 0.2s ease;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}

a.status-card {
  display: flex;
}

.status-card-arrow {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.1rem;
  font-weight: 700;
  color: #94a3b8;
  opacity: 0;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.status-card:hover .status-card-arrow {
  opacity: 1;
  transform: translateY(-50%) translateX(2px);
}

.status-card.theme-laki-laki:hover .status-card-arrow {
  color: #0f766e;
}

.status-card.theme-perempuan:hover .status-card-arrow {
  color: #7c3aed;
}

.status-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

/* Pending: dashed border, muted */
.status-card:not(.completed) {
  border-style: dashed;
  background: #fafbfc;
}

.status-card.theme-laki-laki:not(.completed) {
  border-color: #99f6e4;
  background: linear-gradient(135deg, #fafbfc 0%, #f0fdfa 100%);
}

.status-card.theme-perempuan:not(.completed) {
  border-color: #ddd6fe;
  background: linear-gradient(135deg, #fafbfc 0%, #f5f3ff 100%);
}

.status-card.theme-laki-laki.completed {
  border-left: 4px solid #0f766e;
  background: #f0fdfa;
  border-color: #99f6e4;
}

.status-card.theme-perempuan.completed {
  border-left: 4px solid #7c3aed;
  background: #f5f3ff;
  border-color: #ddd6fe;
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
  color: #64748b;
  background: #e2e8f0;
}

.status-card.theme-laki-laki .status-card-step-num {
  background: #99f6e4;
  color: #0f766e;
}

.status-card.theme-perempuan .status-card-step-num {
  background: #ddd6fe;
  color: #5b21b6;
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
}

.status-card-icon.icon-permohonan {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.2);
}

.status-card-icon.icon-clearance {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.status-card-icon.icon-asrama {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
}

.status-card-icon.icon-kurikulum {
  background: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%);
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
}

.status-card-icon.icon-administrasi {
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  box-shadow: 0 2px 8px rgba(100, 116, 139, 0.2);
}

.status-card.completed .status-card-icon {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
}

.icon-emoji {
  line-height: 1;
}

.status-card-body {
  flex: 1;
  min-width: 0;
  padding-top: 0.1rem;
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
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
  line-height: 1.55;
  font-style: italic;
}

.status-card:not(.completed) .status-card-desc {
  color: #94a3b8;
}

.status-card-badge {
  position: absolute;
  top: 1.25rem;
  right: 1.25rem;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #22c55e;
  color: #fff;
  border-radius: 50%;
  font-size: 0.95rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
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
  color: #94a3b8;
}

.pending-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
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

.theme-laki-laki .profile-avatar {
  background: #99f6e4;
  color: #0f766e;
}

.theme-perempuan .profile-avatar {
  background: #ddd6fe;
  color: #5b21b6;
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

.select-tahun:focus {
  outline: none;
  border-color: #0f766e;
}

.theme-perempuan .select-tahun:focus {
  border-color: #7c3aed;
}
</style>
