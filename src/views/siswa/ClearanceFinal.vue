<template>
  <div class="clearance-page" :class="themeClass">
    <!-- Hero -->
    <div class="page-hero" :class="themeClass">
      <div class="hero-icon">🎓</div>
      <h1 class="hero-title">Clearance Slip — Final Test</h1>
      <p class="hero-subtitle">Status clearance ujian akhir semester (Final Test).</p>
      <div class="hero-badge-wrap">
        <span class="hero-badge">{{ tahunSemester }}</span>
      </div>
    </div>

    <div class="clearance-layout">
      <main class="clearance-main">
        <!-- Status -->
        <div class="card-status-wrap" :class="themeClass">
          <div class="status-icon-wrap">
            <span class="status-icon">{{ statusIcon }}</span>
          </div>
          <div class="status-content">
            <h2 class="status-title">Status Clearance Final Test</h2>
            <div class="status-badge" :class="statusClass">{{ statusLabel }}</div>
            <p class="status-desc">Status clearance Final Test Anda akan tampil di sini setelah data tersedia.</p>
          </div>
        </div>

        <!-- Detail -->
        <div class="card-detail" :class="themeClass">
          <h3 class="card-detail-title">Detail Clearance</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Semester</span>
              <span class="detail-value">{{ tahunSemester }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Jenis</span>
              <span class="detail-value">Ujian Akhir Semester (Final Test)</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Tanggal Disetujui</span>
              <span class="detail-value">—</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Disetujui Oleh</span>
              <span class="detail-value">—</span>
            </div>
          </div>
        </div>
      </main>

      <aside class="clearance-sidebar">
        <div class="sidebar-card" :class="themeClass">
          <h3 class="sidebar-title">Tahun Ajaran & Semester</h3>
          <select v-model="tahunSemester" class="select-tahun">
            <option value="2025/2026 - GENAP">2025/2026 - GENAP</option>
            <option value="2025/2026 - GANJIL">2025/2026 - GANJIL</option>
            <option value="2024/2025 - GENAP">2024/2025 - GENAP</option>
          </select>
        </div>
        <div class="sidebar-card info" :class="themeClass">
          <h3 class="sidebar-title">Informasi</h3>
          <ul class="info-list">
            <li>Clearance Final Test diperlukan untuk mengikuti ujian akhir semester.</li>
            <li>Pastikan semua kewajiban akademik dan administrasi telah diselesaikan.</li>
            <li>Status akan diperbarui setelah verifikasi oleh pihak sekolah.</li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const tahunSemester = ref('2025/2026 - GENAP');
const statusClearance = ref(''); // 'pending' | 'approved' | ''

const statusLabel = computed(() => {
  if (statusClearance.value === 'approved') return 'Disetujui';
  if (statusClearance.value === 'pending') return 'Menunggu Verifikasi';
  return 'Belum ada data';
});
const statusIcon = computed(() => {
  if (statusClearance.value === 'approved') return '✓';
  if (statusClearance.value === 'pending') return '⏳';
  return '📄';
});
const statusClass = computed(() => {
  if (statusClearance.value === 'approved') return 'badge-ok';
  if (statusClearance.value === 'pending') return 'badge-pending';
  return 'badge-empty';
});
</script>

<style scoped>
.clearance-page {
  padding-bottom: 2rem;
}

.page-hero {
  padding: 2rem 1.75rem;
  border-radius: 20px;
  margin-bottom: 1.75rem;
  text-align: center;
  position: relative;
  overflow: hidden;
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
  font-size: 1.5rem;
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

.clearance-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .clearance-layout {
    grid-template-columns: 1fr;
  }
}

.clearance-main {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-status-wrap {
  display: flex;
  align-items: flex-start;
  gap: 1.5rem;
  padding: 1.75rem 2rem;
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.card-status-wrap.theme-laki-laki {
  border-left: 5px solid #0f766e;
}

.card-status-wrap.theme-perempuan {
  border-left: 5px solid #7c3aed;
}

.status-icon-wrap {
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  background: #f1f5f9;
}

.card-status-wrap.theme-laki-laki .status-icon-wrap {
  background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
}

.card-status-wrap.theme-perempuan .status-icon-wrap {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
}

.status-content {
  flex: 1;
  min-width: 0;
}

.status-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.75rem 0;
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 1.25rem;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}

.status-badge.badge-ok {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.badge-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.badge-empty {
  background: #f1f5f9;
  color: #64748b;
}

.status-desc {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.card-detail {
  padding: 1.5rem 2rem;
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.card-detail.theme-laki-laki {
  border-top: 3px solid #0f766e;
}

.card-detail.theme-perempuan {
  border-top: 3px solid #7c3aed;
}

.card-detail-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 1.25rem 0;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem 1.5rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.detail-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
}

.clearance-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.sidebar-card {
  padding: 1.25rem 1.5rem;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
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

.select-tahun {
  width: 100%;
  padding: 0.7rem 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}

.select-tahun:focus {
  outline: none;
  border-color: #0f766e;
}

.sidebar-card.theme-perempuan .select-tahun:focus {
  border-color: #7c3aed;
}

.info-list {
  margin: 0;
  padding-left: 1.2rem;
  color: #475569;
  font-size: 0.9rem;
  line-height: 1.7;
}

.info-list li {
  margin-bottom: 0.5rem;
}
</style>
