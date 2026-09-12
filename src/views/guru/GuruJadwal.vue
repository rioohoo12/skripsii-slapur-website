<template>
  <section class="panel">
    <div class="head">
      <div>
        <h2>Jadwal Mengajar</h2>
        <p>Daftar hari, jam, mata pelajaran, ruang, dan kelas yang diajar.</p>
      </div>
      <div class="legend">
        <span><i class="dot done"></i> Selesai</span>
        <span><i class="dot next"></i> Berikutnya</span>
      </div>
    </div>

    <div class="schedule-grid">
      <article v-for="day in schedule" :key="day.name" class="day-card">
        <h3>{{ day.name }}</h3>
        <ul>
          <li v-for="item in day.items" :key="item.time + item.className">
            <p class="time">{{ item.time }}</p>
            <p class="title">{{ item.subject }} - {{ item.className }}</p>
            <p class="meta">Ruang {{ item.room }}</p>
            <span class="badge" :class="{ next: item.status === 'Berikutnya' }">{{ item.status }}</span>
          </li>
        </ul>
      </article>
    </div>
  </section>
</template>

<script setup>
const schedule = [
  {
    name: 'Senin',
    items: [
      { time: '07:30 - 08:50', subject: 'Matematika', className: 'X IPA 1', room: 'R-201', status: 'Selesai' },
      { time: '09:10 - 10:30', subject: 'Matematika', className: 'X IPA 2', room: 'R-203', status: 'Berikutnya' },
    ],
  },
  {
    name: 'Selasa',
    items: [
      { time: '08:00 - 09:20', subject: 'Aljabar', className: 'XI IPA 1', room: 'LAB-1', status: 'Terjadwal' },
      { time: '10:00 - 11:20', subject: 'Aljabar', className: 'XI IPA 2', room: 'LAB-1', status: 'Terjadwal' },
    ],
  },
  {
    name: 'Rabu',
    items: [{ time: '07:30 - 08:50', subject: 'Matematika', className: 'X IPA 3', room: 'R-205', status: 'Terjadwal' }],
  },
];
</script>

<style scoped>
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
  margin-bottom: 1rem;
}
h2,
h3 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.25rem 0 0;
  color: #64748b;
}
.legend {
  display: flex;
  gap: 0.8rem;
  font-size: 0.8rem;
  color: #475569;
  align-items: center;
}
.dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  display: inline-block;
}
.dot.done {
  background: #22c55e;
}
.dot.next {
  background: #2563eb;
}
.schedule-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.75rem;
}
.day-card {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.8rem;
}
ul {
  list-style: none;
  margin: 0.6rem 0 0;
  padding: 0;
  display: grid;
  gap: 0.55rem;
}
li {
  background: #f8fafc;
  border-radius: 10px;
  padding: 0.55rem 0.6rem;
}
.time {
  color: #334155;
  font-size: 0.77rem;
  font-weight: 700;
}
.title {
  color: #1e293b;
  font-weight: 600;
  margin-top: 0.15rem;
}
.meta {
  font-size: 0.78rem;
}
.badge {
  display: inline-block;
  margin-top: 0.35rem;
  background: #e2e8f0;
  color: #334155;
  font-size: 0.72rem;
  border-radius: 999px;
  padding: 0.2rem 0.5rem;
}
.badge.next {
  background: #dbeafe;
  color: #1d4ed8;
}
@media (max-width: 980px) {
  .schedule-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 640px) {
  .head {
    flex-direction: column;
  }
}
</style>
