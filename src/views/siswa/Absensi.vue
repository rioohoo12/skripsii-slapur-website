<template>
  <div class="absensi-page">
    <h2 class="page-heading">Daftar Absensi Kelas</h2>

    <div class="absensi-layout">
      <!-- Tabel daftar absensi -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-tabel">
        <template #header>Daftar Absensi Kelas</template>
        <div class="table-wrap">
          <table class="absensi-table">
            <thead>
              <tr>
                <th class="col-no">No.</th>
                <th class="col-pct">Kehadiran</th>
                <th class="col-action">Detail</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in daftarAbsensi" :key="i">
                <td class="col-no">{{ i + 1 }}</td>
                <td class="col-pct">{{ row.kehadiran }}%</td>
                <td class="col-action">
                  <button type="button" class="btn-detail" :class="themeClass">Detail</button>
                </td>
              </tr>
              <tr v-if="!daftarAbsensi.length" class="empty-row">
                <td colspan="3">Belum ada data absensi</td>
              </tr>
            </tbody>
          </table>
        </div>
      </PageCard>

      <!-- Sidebar: filter + gauge -->
      <aside class="absensi-sidebar">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-filter">
          <template #header>Tahun Ajaran & Semester</template>
          <div class="filter-body">
            <select v-model="tahunSemester" class="select-semester">
              <option value="2025/2026 - GENAP">2025/2026 - GENAP</option>
              <option value="2025/2026 - GANJIL">2025/2026 - GANJIL</option>
              <option value="2024/2025 - GENAP">2024/2025 - GENAP</option>
            </select>
          </div>
        </PageCard>

        <PageCard :jenis-kelamin="jenisKelamin" class="card-gauge">
          <template #header>Rata-rata Kehadiran</template>
          <div class="gauge-wrap">
            <div class="gauge" :class="themeClass">
              <div class="gauge-bg"></div>
              <div class="gauge-fill" :style="{ '--pct': rataRataKehadiran }"></div>
              <div class="gauge-needle" :style="{ '--pct': rataRataKehadiran }"></div>
              <div class="gauge-value">{{ rataRataKehadiran }}%</div>
            </div>
            <p class="gauge-label">Rata-rata Kehadiran</p>
          </div>
        </PageCard>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const tahunSemester = ref('2025/2026 - GENAP');
// Data kosong – gambaran umum tampilan (isi dari API nanti)
const daftarAbsensi = ref([]);

const rataRataKehadiran = computed(() => {
  if (!daftarAbsensi.value.length) return 0;
  const total = daftarAbsensi.value.reduce((acc, r) => acc + (r.kehadiran || 0), 0);
  return (total / daftarAbsensi.value.length).toFixed(2);
});
</script>

<style scoped>
.absensi-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.page-heading {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.absensi-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .absensi-layout {
    grid-template-columns: 1fr;
  }
}

.card-tabel :deep(.page-card-body) {
  padding: 0;
}

.table-wrap {
  overflow-x: auto;
}

.absensi-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.absensi-table th,
.absensi-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.absensi-table th {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
}

.absensi-table .col-no {
  width: 3.5rem;
  text-align: center;
}

.absensi-table .col-pct {
  width: 7rem;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.absensi-table .col-action {
  width: 6rem;
  text-align: center;
}

.absensi-table tbody tr:hover {
  background: #fafafa;
}

.absensi-table .empty-row td {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

.btn-detail {
  padding: 0.4rem 0.85rem;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-detail.theme-laki-laki {
  background: #0ea5e9;
  color: #fff;
}

.btn-detail.theme-perempuan {
  background: #8b5cf6;
  color: #fff;
}

.btn-detail:hover {
  opacity: 0.9;
}

/* Sidebar */
.absensi-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-filter :deep(.page-card-body),
.card-gauge :deep(.page-card-body) {
  padding: 1.25rem;
}

.select-semester {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}

.select-semester:focus {
  outline: none;
  border-color: #0f766e;
}

.theme-perempuan .select-semester:focus {
  border-color: #7c3aed;
}

/* Gauge */
.gauge-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem 0;
}

.gauge {
  position: relative;
  width: 180px;
  height: 100px;
  margin-bottom: 0.75rem;
}

.gauge-bg,
.gauge-fill {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 90px 90px 0 0;
  overflow: hidden;
}

.gauge-bg {
  background: #f1f5f9;
}

.gauge-fill {
  background: conic-gradient(
    from 180deg at 50% 100%,
    var(--gauge-color, #f97316) 0deg,
    var(--gauge-color, #f97316) calc(var(--pct, 0) * 1.8deg),
    #e2e8f0 calc(var(--pct, 0) * 1.8deg)
  );
  mask: radial-gradient(farthest-side at 50% 100%, transparent 65%, black 66%);
  -webkit-mask: radial-gradient(farthest-side at 50% 100%, transparent 65%, black 66%);
}

.gauge.theme-laki-laki {
  --gauge-color: #0ea5e9;
}

.gauge.theme-perempuan {
  --gauge-color: #8b5cf6;
}

.gauge-needle {
  position: absolute;
  left: 50%;
  bottom: 0;
  width: 4px;
  height: 42%;
  background: #1e293b;
  border-radius: 2px;
  transform-origin: 50% 100%;
  transform: translateX(-50%) rotate(calc(-90deg + (var(--pct, 0) * 1.8deg)));
  transition: transform 0.4s ease-out;
}

.gauge-value {
  position: absolute;
  left: 50%;
  bottom: 18px;
  transform: translateX(-50%);
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
}

.gauge-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  margin: 0;
}
</style>
