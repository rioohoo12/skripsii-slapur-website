<template>
  <div class="review-kelas-page">
    <h2 class="page-heading">Review Kelas</h2>
    <p class="page-desc">Jadwal mata pelajaran per hari. Lihat mata pelajaran apa saja pada hari tertentu.</p>

    <div class="review-layout">
      <!-- Tabel jadwal: mata pelajaran - hari -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-tabel">
        <template #header>Jadwal Mata Pelajaran</template>
        <div class="table-wrap">
          <table class="schedule-table">
            <thead>
              <tr>
                <th class="col-no">No.</th>
                <th>Mata Pelajaran</th>
                <th class="col-hari">Hari</th>
                <th class="col-jam">Jam</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in jadwalFiltered" :key="i">
                <td class="col-no">{{ i + 1 }}</td>
                <td>{{ row.mataPelajaran }}</td>
                <td class="col-hari">{{ row.hari }}</td>
                <td class="col-jam">{{ row.jam }}</td>
              </tr>
              <tr v-if="!jadwalFiltered.length" class="empty-row">
                <td colspan="4">Belum ada jadwal mata pelajaran</td>
              </tr>
            </tbody>
          </table>
        </div>
      </PageCard>

      <!-- Sidebar: filter -->
      <aside class="review-sidebar">
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

        <PageCard :jenis-kelamin="jenisKelamin" class="card-hari">
          <template #header>Lihat per Hari</template>
          <div class="hari-list">
            <button
              v-for="d in hariList"
              :key="d.value"
              type="button"
              class="btn-hari"
              :class="[themeClass, { active: filterHari === d.value }]"
              @click="filterHari = filterHari === d.value ? '' : d.value"
            >
              {{ d.label }}
            </button>
          </div>
          <p v-if="filterHari" class="filter-info">Menampilkan jadwal hari: <strong>{{ labelHari(filterHari) }}</strong></p>
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
const filterHari = ref('');

// Data kosong – gambaran umum (isi dari API nanti)
const jadwalMapel = ref([]);

const hariList = [
  { value: 'senin', label: 'Senin' },
  { value: 'selasa', label: 'Selasa' },
  { value: 'rabu', label: 'Rabu' },
  { value: 'kamis', label: 'Kamis' },
  { value: 'jumat', label: 'Jumat' },
  { value: 'sabtu', label: 'Sabtu' },
];

function labelHari(value) {
  return hariList.find((h) => h.value === value)?.label || value;
}

const jadwalFiltered = computed(() => {
  if (!filterHari.value) return jadwalMapel.value;
  return jadwalMapel.value.filter((r) => (r.hari || '').toLowerCase() === filterHari.value);
});
</script>

<style scoped>
.review-kelas-page {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.page-heading {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.page-desc {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0;
}

.review-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .review-layout {
    grid-template-columns: 1fr;
  }
}

.card-tabel :deep(.page-card-body) {
  padding: 0;
}

.table-wrap {
  overflow-x: auto;
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.schedule-table th,
.schedule-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.schedule-table th {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
}

.schedule-table .col-no {
  width: 3.5rem;
  text-align: center;
}

.schedule-table .col-hari {
  width: 7rem;
}

.schedule-table .col-jam {
  width: 8rem;
  font-variant-numeric: tabular-nums;
}

.schedule-table tbody tr:hover {
  background: #fafafa;
}

.schedule-table .empty-row td {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

/* Sidebar */
.review-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-filter :deep(.page-card-body),
.card-hari :deep(.page-card-body) {
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

.hari-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.btn-hari {
  padding: 0.5rem 0.85rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #fff;
  font-size: 0.85rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.btn-hari:hover {
  background: #f8fafc;
  color: #1e293b;
}

.btn-hari.theme-laki-laki.active {
  background: #ccfbf1;
  border-color: #0f766e;
  color: #0f766e;
}

.btn-hari.theme-perempuan.active {
  background: #ede9fe;
  border-color: #7c3aed;
  color: #5b21b6;
}

.filter-info {
  margin: 1rem 0 0;
  font-size: 0.85rem;
  color: #64748b;
}

.filter-info strong {
  color: #1e293b;
}
</style>
