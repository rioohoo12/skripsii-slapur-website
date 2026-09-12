<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Nilai Siswa</h2>
          <p>Kelola nilai tugas, UTS, UAS, nilai akhir, dan rekap kelas.</p>
        </div>
        <button class="primary-btn" type="button">Input Nilai Baru</button>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Tugas</th>
              <th>UTS</th>
              <th>UAS</th>
              <th>Nilai Akhir</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gradeRows" :key="item.name">
              <td>{{ item.name }}</td>
              <td>{{ item.tugas }}</td>
              <td>{{ item.uts }}</td>
              <td>{{ item.uas }}</td>
              <td><strong>{{ item.final }}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="summary-grid">
      <article v-for="item in summaries" :key="item.label" class="summary-card">
        <p class="label">{{ item.label }}</p>
        <p class="value">{{ item.value }}</p>
      </article>
    </section>
  </div>
</template>

<script setup>
const gradeRows = [
  { name: 'Adit Saputra', tugas: 84, uts: 82, uas: 88, final: 85 },
  { name: 'Bunga Maharani', tugas: 90, uts: 88, uas: 92, final: 90 },
  { name: 'Cahyo Putra', tugas: 76, uts: 78, uas: 80, final: 78 },
  { name: 'Dina Lestari', tugas: 89, uts: 87, uas: 91, final: 89 },
];

const summaries = [
  { label: 'Rata-rata Kelas', value: '85.5' },
  { label: 'Nilai Tertinggi', value: '92' },
  { label: 'Nilai Terendah', value: '72' },
  { label: 'Siswa Remedial', value: '3 siswa' },
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
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.8rem;
}
h2 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 10px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
}
.table-wrap {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.68rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.88rem;
}
th {
  color: #475569;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.7rem;
}
.summary-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.8rem;
}
.label {
  margin: 0;
  font-size: 0.78rem;
}
.value {
  margin: 0.25rem 0 0;
  color: #0f172a;
  font-size: 1.3rem;
  font-weight: 800;
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
