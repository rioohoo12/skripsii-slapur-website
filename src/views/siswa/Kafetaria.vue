<template>
  <div class="kafetaria-page">
    <h2 class="page-heading">Laporan Makan di Kafetaria</h2>

    <div class="kafetaria-layout">
      <!-- Tabel laporan -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-tabel">
        <template #header>Laporan Makan</template>
        <div class="table-wrap">
          <table class="meal-table">
            <thead>
              <tr>
                <th class="col-no">No.</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Jenis Makan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in laporanMakan" :key="i">
                <td class="col-no">{{ i + 1 }}</td>
                <td>{{ row.tanggal }}</td>
                <td>{{ row.waktu }}</td>
                <td>{{ row.jenisMakan }}</td>
              </tr>
              <tr v-if="!laporanMakan.length" class="empty-row">
                <td colspan="4">Belum ada data laporan makan</td>
              </tr>
            </tbody>
          </table>
        </div>
      </PageCard>

      <!-- Sidebar: filter + kartu ringkasan -->
      <aside class="kafetaria-sidebar">
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

        <div class="summary-cards">
          <div class="summary-card" :class="themeClass">
            <div class="summary-icon">🍽️</div>
            <div class="summary-text">
              <span class="summary-value">{{ nomorKartuMakan || '—' }}</span>
              <span class="summary-label">Nomor Kartu Makan</span>
            </div>
          </div>
          <div class="summary-card" :class="themeClass">
            <div class="summary-icon">📋</div>
            <div class="summary-text">
              <span class="summary-value">{{ jumlahMakan }}</span>
              <span class="summary-label">Jumlah Makan</span>
            </div>
          </div>
        </div>
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
// Data kosong – gambaran tampilan saja, isi dari API nanti
const laporanMakan = ref([]);
const nomorKartuMakan = ref('');
const jumlahMakan = computed(() => laporanMakan.value.length);
</script>

<style scoped>
.kafetaria-page {
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

.kafetaria-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .kafetaria-layout {
    grid-template-columns: 1fr;
  }
}

.card-tabel :deep(.page-card-body) {
  padding: 0;
}

.table-wrap {
  overflow-x: auto;
}

.meal-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.meal-table th,
.meal-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.meal-table th {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
}

.meal-table .col-no {
  width: 4rem;
  text-align: center;
}

.meal-table tbody tr:hover {
  background: #fafafa;
}

.meal-table .empty-row td {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

.kafetaria-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-filter :deep(.page-card-body) {
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

.summary-cards {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.summary-card {
  padding: 1.25rem;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: box-shadow 0.2s;
}

.summary-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.summary-card.theme-laki-laki {
  border-left: 4px solid #0f766e;
}

.summary-card.theme-perempuan {
  border-left: 4px solid #7c3aed;
}

.summary-icon {
  font-size: 2rem;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fef3c7;
  border-radius: 12px;
}

.summary-text {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.summary-card .summary-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
}

.summary-card .summary-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}
</style>
