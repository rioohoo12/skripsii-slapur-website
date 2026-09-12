<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Absensi Siswa</h2>
          <p>Catat status hadir, izin, sakit, atau alpha setiap pertemuan.</p>
        </div>
        <button class="primary-btn" type="button">Simpan Absensi</button>
      </div>
      <div class="chips">
        <span class="hadir">Hadir</span>
        <span class="izin">Izin</span>
        <span class="sakit">Sakit</span>
        <span class="alpha">Alpha</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>NIS</th>
              <th>Status Hari Ini</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in attendanceRows" :key="row.nis">
              <td>{{ row.name }}</td>
              <td>{{ row.nis }}</td>
              <td><span class="badge" :class="row.status.toLowerCase()">{{ row.status }}</span></td>
              <td>{{ row.note }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <h3>Riwayat Absensi Mingguan</h3>
      <div class="history-grid">
        <article v-for="item in weeklySummary" :key="item.label" class="history-card">
          <p class="label">{{ item.label }}</p>
          <p class="value">{{ item.value }}</p>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
const attendanceRows = [
  { name: 'Adit Saputra', nis: '240011', status: 'Hadir', note: '-' },
  { name: 'Bunga Maharani', nis: '240012', status: 'Izin', note: 'Surat orang tua' },
  { name: 'Cahyo Putra', nis: '240013', status: 'Sakit', note: 'Demam' },
  { name: 'Dina Lestari', nis: '240014', status: 'Alpha', note: 'Belum ada keterangan' },
];

const weeklySummary = [
  { label: 'Total Hadir', value: '176' },
  { label: 'Total Izin', value: '8' },
  { label: 'Total Sakit', value: '5' },
  { label: 'Total Alpha', value: '3' },
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
  margin-bottom: 0.75rem;
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
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 10px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
}
.chips {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.8rem;
}
.chips span {
  border-radius: 999px;
  padding: 0.23rem 0.55rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.chips .hadir {
  background: #dcfce7;
  color: #166534;
}
.chips .izin {
  background: #fef3c7;
  color: #92400e;
}
.chips .sakit {
  background: #dbeafe;
  color: #1d4ed8;
}
.chips .alpha {
  background: #fee2e2;
  color: #991b1b;
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
  padding: 0.65rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.88rem;
}
th {
  color: #475569;
}
.badge {
  border-radius: 999px;
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.badge.hadir {
  background: #dcfce7;
  color: #166534;
}
.badge.izin {
  background: #fef3c7;
  color: #92400e;
}
.badge.sakit {
  background: #dbeafe;
  color: #1d4ed8;
}
.badge.alpha {
  background: #fee2e2;
  color: #991b1b;
}
.history-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.7rem;
  margin-top: 0.8rem;
}
.history-card {
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
  font-weight: 700;
  font-size: 1.25rem;
}
@media (max-width: 980px) {
  .history-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 640px) {
  .head {
    flex-direction: column;
    align-items: flex-start;
  }
  .history-grid {
    grid-template-columns: 1fr;
  }
}
</style>
