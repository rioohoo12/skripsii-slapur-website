<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Laporan / Rekap</h2>
          <p>Rekap absensi, nilai, serta ekspor laporan dalam format PDF atau Excel.</p>
        </div>
        <div class="actions">
          <button type="button" class="ghost-btn">Export PDF</button>
          <button type="button" class="primary-btn">Export Excel</button>
        </div>
      </div>
      <div class="summary-grid">
        <article v-for="item in summary" :key="item.label" class="summary-card">
          <p class="label">{{ item.label }}</p>
          <p class="value">{{ item.value }}</p>
        </article>
      </div>
    </section>

    <section class="panel">
      <h3>Rekap Per Kelas</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Kelas</th>
              <th>Kehadiran</th>
              <th>Rata-rata Nilai</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.className">
              <td>{{ row.className }}</td>
              <td>{{ row.attendance }}</td>
              <td>{{ row.score }}</td>
              <td><span class="status">{{ row.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
const summary = [
  { label: 'Kehadiran Bulanan', value: '93.8%' },
  { label: 'Rata-rata Nilai', value: '84.6' },
  { label: 'Laporan Terbit', value: '12' },
  { label: 'Perlu Tindak Lanjut', value: '2 kelas' },
];

const rows = [
  { className: 'X IPA 1', attendance: '95%', score: '86.2', status: 'Baik' },
  { className: 'X IPA 2', attendance: '92%', score: '83.1', status: 'Baik' },
  { className: 'XI IPA 1', attendance: '90%', score: '82.4', status: 'Pemantauan' },
  { className: 'XI IPA 2', attendance: '94%', score: '86.6', status: 'Baik' },
];
</script>

<style scoped>
.page-grid {
  display: grid;
  gap: 1rem;
}
.panel {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}
.head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.8rem;
}
h2,
h3 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
}
.actions {
  display: flex;
  gap: 0.45rem;
}
.primary-btn,
.ghost-btn {
  border-radius: 10px;
  padding: 0.52rem 0.85rem;
  font-size: 0.82rem;
  cursor: pointer;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.7rem;
}
.summary-card {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.7rem;
  background: #f8fafc;
}
.label {
  margin: 0;
  font-size: 0.78rem;
}
.value {
  margin: 0.2rem 0 0;
  color: #0f172a;
  font-size: 1.2rem;
  font-weight: 800;
}
.table-wrap {
  overflow-x: auto;
  margin-top: 0.75rem;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.65rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.88rem;
}
th {
  color: #475569;
}
.status {
  background: #dcfce7;
  color: #166534;
  border-radius: 999px;
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
}
@media (max-width: 980px) {
  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 640px) {
  .head {
    flex-direction: column;
    align-items: flex-start;
  }
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>
