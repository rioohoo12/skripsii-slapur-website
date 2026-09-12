<template>
  <div class="kurikulum-page" :class="themeClass">
    <!-- Header -->
    <div class="page-hero" :class="themeClass">
      <div class="hero-icon">📚</div>
      <h1 class="hero-title">Kurikulum</h1>
      <p class="hero-subtitle">Mata pelajaran yang akan dipelajari pada semester ini.</p>
      <div class="hero-badge-wrap">
        <span class="hero-badge">{{ tahunSemester }}</span>
      </div>
    </div>

    <div class="kurikulum-layout">
      <!-- Daftar mata pelajaran -->
      <main class="kurikulum-main">
        <div class="section-head">
          <h2 class="section-title">Mata Pelajaran Semester Ini</h2>
          <span class="section-count">{{ daftarMapel.length }} mata pelajaran</span>
        </div>

        <p v-if="loading" class="info-msg">Memuat kurikulum...</p>
        <p v-else-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <p v-else-if="infoMsg" class="info-msg">{{ infoMsg }}</p>

        <div class="mapel-grid">
          <article
            v-for="(mapel, i) in daftarMapel"
            :key="mapel.kode"
            class="mapel-card"
            :class="themeClass"
          >
            <div class="mapel-card-num">{{ i + 1 }}</div>
            <div class="mapel-card-icon" :class="themeClass">
              <span>{{ mapel.icon }}</span>
            </div>
            <div class="mapel-card-body">
              <span class="mapel-kode">{{ mapel.kode }}</span>
              <h3 class="mapel-nama">{{ mapel.nama }}</h3>
            </div>
          </article>
        </div>

        <p v-if="!loading && !errorMsg && !infoMsg && !daftarMapel.length" class="empty-msg">
          Belum ada data mata pelajaran untuk semester ini.
        </p>
      </main>

      <!-- Sidebar -->
      <aside class="kurikulum-sidebar">
        <div class="sidebar-card" :class="themeClass">
          <h3 class="sidebar-title">Tahun Ajaran & Semester</h3>
          <select v-model="tahunSemester" class="select-semester">
            <option value="2025/2026 - GENAP">2025/2026 - GENAP</option>
            <option value="2025/2026 - GANJIL">2025/2026 - GANJIL</option>
            <option value="2024/2025 - GENAP">2024/2025 - GENAP</option>
          </select>
        </div>

        <div class="sidebar-card summary" :class="themeClass">
          <h3 class="sidebar-title">Ringkasan</h3>
          <div class="summary-row">
            <span class="summary-label">Total Mata Pelajaran</span>
            <span class="summary-value">{{ daftarMapel.length }}</span>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { pendaftaranApi } from '@/api/pendaftaran';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const tahunSemester = ref('2025/2026 - GENAP');

const daftarMapel = ref([]);
const loading = ref(false);
const errorMsg = ref('');
const infoMsg = ref('');

async function loadKurikulum() {
  loading.value = true;
  errorMsg.value = '';
  infoMsg.value = '';
  try {
    const status = await pendaftaranApi.getStatus();
    const profile = status.profile;
    const payment = status.payment;

    if (!profile || !profile.kelas_yang_didaftar) {
      infoMsg.value =
        'Kurikulum akan muncul setelah Anda mengisi biodata pendaftaran (kelas yang didaftar). Silakan lengkapi biodata terlebih dahulu.';
      daftarMapel.value = [];
      return;
    }
    if (!payment || !(payment.status === 'terverifikasi' || payment.nominal_dibayar >= payment.nominal_60_percent)) {
      infoMsg.value =
        'Daftar mata pelajaran akan aktif setelah pembayaran pendaftaran minimal 60% tercatat atau terverifikasi.';
      daftarMapel.value = [];
      return;
    }

    const tingkat = profile.kelas_yang_didaftar;
    const res = await pendaftaranApi.getKurikulum(tingkat);
    const list = Array.isArray(res.list) ? res.list.filter((m) => m.tingkat === tingkat) : [];

    daftarMapel.value = list.map((item) => ({
      kode: `MAPEL-${item.urutan}`,
      nama: item.nama_pelajaran,
      icon: pickIcon(item.nama_pelajaran),
    }));
  } catch (e) {
    console.error(e);
    errorMsg.value = e.message || 'Gagal memuat kurikulum. Coba beberapa saat lagi.';
    daftarMapel.value = [];
  } finally {
    loading.value = false;
  }
}

function pickIcon(nama) {
  const lower = String(nama || '').toLowerCase();
  if (lower.includes('matematika')) return '🔢';
  if (lower.includes('fisika') || lower.includes('kimia') || lower.includes('biologi') || lower.includes('ipa'))
    return '⚛️';
  if (lower.includes('bahasa indonesia')) return '📖';
  if (lower.includes('bahasa inggris')) return '🌐';
  if (lower.includes('sejarah')) return '📜';
  if (lower.includes('agama')) return '🙏';
  if (lower.includes('sen') || lower.includes('seni')) return '🎨';
  if (lower.includes('pjok') || lower.includes('jasmani')) return '⚽';
  if (lower.includes('tik') || lower.includes('informatika')) return '💻';
  if (lower.includes('ekonomi')) return '💰';
  if (lower.includes('pkn') || lower.includes('kewarganegaraan')) return '🇮🇩';
  return '📚';
}

onMounted(() => {
  loadKurikulum();
});
</script>

<style scoped>
.kurikulum-page {
  padding-bottom: 2rem;
}

/* ----- Hero ----- */
.page-hero {
  padding: 2rem 1.75rem;
  border-radius: 20px;
  margin-bottom: 1.75rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-hero::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60%;
  background: linear-gradient(to top, rgba(0,0,0,0.08), transparent);
  pointer-events: none;
}

.page-hero.theme-laki-laki {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%);
  box-shadow: 0 10px 40px rgba(15, 118, 110, 0.25);
}

.page-hero.theme-perempuan {
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #8b5cf6 100%);
  box-shadow: 0 10px 40px rgba(91, 33, 182, 0.25);
}

.hero-icon {
  font-size: 2.5rem;
  margin-bottom: 0.4rem;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
}

.hero-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #fff;
  margin: 0 0 0.3rem 0;
  letter-spacing: -0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.92);
  margin: 0 0 1rem 0;
}

.hero-badge-wrap {
  position: relative;
  z-index: 1;
}

.hero-badge {
  display: inline-block;
  padding: 0.45rem 1.1rem;
  border-radius: 999px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #fff;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}

/* ----- Layout ----- */
.kurikulum-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .kurikulum-layout {
    grid-template-columns: 1fr;
  }
}

.kurikulum-main {
  min-width: 0;
}

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
}

.section-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.section-count {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}
.info-msg {
  margin: 0 0 1rem 0;
  color: #64748b;
  font-size: 0.95rem;
}
.error-msg {
  margin: 0 0 1rem 0;
  color: #b91c1c;
  font-size: 0.95rem;
  font-weight: 600;
}

/* ----- Mata pelajaran grid ----- */
.mapel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.25rem;
}

.mapel-card {
  position: relative;
  background: #fff;
  border-radius: 18px;
  padding: 1.35rem 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.mapel-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.mapel-card.theme-laki-laki {
  border-left: 4px solid #0f766e;
}

.mapel-card.theme-perempuan {
  border-left: 4px solid #7c3aed;
}

.mapel-card-num {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 800;
  color: #64748b;
  background: #f1f5f9;
}

.mapel-card.theme-laki-laki .mapel-card-num {
  background: #ccfbf1;
  color: #0f766e;
}

.mapel-card.theme-perempuan .mapel-card-num {
  background: #ede9fe;
  color: #5b21b6;
}

.mapel-card-icon {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
}

.mapel-card-icon.theme-laki-laki {
  background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
  box-shadow: 0 4px 12px rgba(15, 118, 110, 0.15);
}

.mapel-card-icon.theme-perempuan {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
}

.mapel-card-body {
  flex: 1;
  min-width: 0;
  padding-right: 2rem;
}

.mapel-kode {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.02em;
  margin-bottom: 0.25rem;
}

.mapel-nama {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  line-height: 1.3;
}

.empty-msg {
  text-align: center;
  color: #94a3b8;
  font-size: 0.95rem;
  padding: 2rem 1rem;
  margin: 0;
}

/* ----- Sidebar ----- */
.kurikulum-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.sidebar-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.sidebar-card.theme-laki-laki {
  border-top: 3px solid #0f766e;
}

.sidebar-card.theme-perempuan {
  border-top: 3px solid #7c3aed;
}

.sidebar-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 1rem 0;
}

.select-semester {
  width: 100%;
  padding: 0.7rem 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}

.select-semester:focus {
  outline: none;
  border-color: #0f766e;
}

.sidebar-card.theme-perempuan .select-semester:focus {
  border-color: #7c3aed;
}

.summary .summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.summary .summary-row:last-child {
  border-bottom: none;
}

.summary-label {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}

.summary-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f766e;
}

.sidebar-card.theme-perempuan .summary-value {
  color: #5b21b6;
}
</style>
