<template>
  <div class="grade-page">
    <h2 class="page-heading">Grade / Nilai</h2>

    <div class="grade-layout">
      <!-- Tabel nilai -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-tabel">
        <template #header>Daftar Nilai</template>
        <div class="table-wrap">
          <table class="grade-table">
            <thead>
              <tr>
                <th class="col-no">No.</th>
                <th>Mata Pelajaran</th>
                <th class="col-nilai">Tugas</th>
                <th class="col-nilai">UTS</th>
                <th class="col-nilai">UAS</th>
                <th class="col-nilai">Nilai Akhir</th>
                <th class="col-grade">Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in daftarNilai" :key="i">
                <td class="col-no">{{ i + 1 }}</td>
                <td>{{ row.mataPelajaran }}</td>
                <td class="col-nilai">{{ row.tugas }}</td>
                <td class="col-nilai">{{ row.uts }}</td>
                <td class="col-nilai">{{ row.uas }}</td>
                <td class="col-nilai">{{ row.nilaiAkhir }}</td>
                <td class="col-grade">
                  <span class="badge-grade" :class="[themeClass, gradeClass(row.grade)]">{{ row.grade }}</span>
                </td>
              </tr>
              <tr v-if="!daftarNilai.length" class="empty-row">
                <td colspan="7">Belum ada data nilai</td>
              </tr>
            </tbody>
          </table>
        </div>
      </PageCard>

      <!-- Sidebar: filter + ringkasan -->
      <aside class="grade-sidebar">
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

        <PageCard :jenis-kelamin="jenisKelamin" class="card-summary">
          <template #header>Ringkasan</template>
          <div class="summary-body">
            <div class="summary-row">
              <span class="summary-label">Rata-rata Nilai</span>
              <span class="summary-value">{{ rataRataNilai }}</span>
            </div>
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
// Data kosong – gambaran tampilan (isi dari API nanti)
const daftarNilai = ref([]);

const rataRataNilai = computed(() => {
  if (!daftarNilai.value.length) return '-';
  const total = daftarNilai.value.reduce((acc, r) => acc + (parseFloat(r.nilaiAkhir) || 0), 0);
  return (total / daftarNilai.value.length).toFixed(2);
});

function gradeClass(grade) {
  if (!grade) return '';
  const g = String(grade).toUpperCase();
  if (['A', 'A-'].includes(g)) return 'grade-a';
  if (['B+', 'B', 'B-'].includes(g)) return 'grade-b';
  if (['C+', 'C', 'C-'].includes(g)) return 'grade-c';
  if (['D'].includes(g)) return 'grade-d';
  return 'grade-e';
}
</script>

<style scoped>
.grade-page {
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

.grade-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .grade-layout {
    grid-template-columns: 1fr;
  }
}

.card-tabel :deep(.page-card-body) {
  padding: 0;
}

.table-wrap {
  overflow-x: auto;
}

.grade-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.grade-table th,
.grade-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.grade-table th {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
}

.grade-table .col-no {
  width: 3.5rem;
  text-align: center;
}

.grade-table .col-nilai {
  width: 5rem;
  text-align: center;
  font-variant-numeric: tabular-nums;
}

.grade-table .col-grade {
  width: 5.5rem;
  text-align: center;
}

.grade-table tbody tr:hover {
  background: #fafafa;
}

.grade-table .empty-row td {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

.badge-grade {
  display: inline-block;
  padding: 0.35rem 0.65rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 700;
}

.badge-grade.grade-a {
  background: #dcfce7;
  color: #166534;
}

.badge-grade.grade-b {
  background: #dbeafe;
  color: #1e40af;
}

.badge-grade.grade-c {
  background: #fef9c3;
  color: #854d0e;
}

.badge-grade.grade-d {
  background: #fed7aa;
  color: #9a3412;
}

.badge-grade.grade-e {
  background: #fee2e2;
  color: #991b1b;
}

.theme-laki-laki .badge-grade:not([class*="grade-"]) {
  background: #ccfbf1;
  color: #0f766e;
}

.theme-perempuan .badge-grade:not([class*="grade-"]) {
  background: #ede9fe;
  color: #5b21b6;
}

/* Sidebar */
.grade-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-filter :deep(.page-card-body),
.card-summary :deep(.page-card-body) {
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

.summary-body {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.summary-row:last-child {
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
  color: #1e293b;
}

.theme-laki-laki .summary-value {
  color: #0f766e;
}

.theme-perempuan .summary-value {
  color: #5b21b6;
}
</style>
