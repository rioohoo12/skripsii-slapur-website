<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Laporan Makan Siswa</h1>
      <p class="page-subtitle">Rekapitulasi kehadiran siswa di ruang makan</p>
    </div>

    <div class="content-card">
      <div class="filters">
        <div class="form-group">
          <label>Pilih Tanggal</label>
          <input type="date" v-model="filterDate" @change="fetchReport" />
        </div>
      </div>

      <!-- Ringkasan -->
      <div class="summary-grid" v-if="!loading">
        <div class="summary-item">
          <span class="label">Pagi</span>
          <span class="value">{{ summary.Pagi || 0 }}</span>
        </div>
        <div class="summary-item">
          <span class="label">Siang</span>
          <span class="value">{{ summary.Siang || 0 }}</span>
        </div>
        <div class="summary-item">
          <span class="label">Sore</span>
          <span class="value">{{ summary.Sore || 0 }}</span>
        </div>
        <div class="summary-item highlight">
          <span class="label">Total Scan</span>
          <span class="value">{{ summary.Total || 0 }}</span>
        </div>
      </div>

      <!-- Tabel Detail -->
      <div class="table-responsive">
        <table class="report-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>Sesi Makan</th>
              <th>Waktu Scan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="text-center">Memuat laporan...</td>
            </tr>
            <tr v-else-if="logs.length === 0">
              <td colspan="4" class="text-center text-muted">Belum ada data kehadiran untuk tanggal ini</td>
            </tr>
            <tr v-for="(log, idx) in logs" :key="log.id" v-else>
              <td>{{ idx + 1 }}</td>
              <td class="fw-medium">{{ log.student_name }}</td>
              <td>
                <span class="badge" :class="log.meal_time.toLowerCase()">
                  {{ log.meal_time }}
                </span>
              </td>
              <td class="time-col">{{ log.scanned_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { request } from '@/api/auth';

const filterDate = ref(new Date().toISOString().split('T')[0]);
const loading = ref(false);
const summary = ref({});
const logs = ref([]);

async function fetchReport() {
  loading.value = true;
  try {
    const res = await request(`/kafetaria/laporan?date=${filterDate.value}`);
    summary.value = res.summary || {};
    logs.value = res.logs || [];
  } catch (err) {
    console.error('Failed to fetch report', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchReport();
});
</script>

<style scoped>
.page-container {
  padding: 2rem 2.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0;
}

.content-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.filters {
  margin-bottom: 2rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  margin-bottom: 0.5rem;
}

input {
  padding: 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
}
input:focus { border-color: #f59e0b; }

.summary-grid {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-item {
  flex: 1;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.summary-item.highlight {
  background: #fffbeb;
  border-color: #fde68a;
}

.summary-item .label {
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
}
.summary-item.highlight .label { color: #d97706; }

.summary-item .value {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
}

.report-table th {
  text-align: left;
  padding: 1rem;
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
  border-bottom: 2px solid #e2e8f0;
}

.report-table td {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.fw-medium { font-weight: 500; color: #1e293b; }
.time-col { color: #64748b; font-family: monospace; font-size: 1.05rem; }

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
}
.badge.pagi { background: #e0f2fe; color: #0284c7; }
.badge.siang { background: #fef3c7; color: #d97706; }
.badge.sore { background: #ede9fe; color: #7c3aed; }

.text-center { text-align: center; }
.text-muted { color: #94a3b8; }
</style>
