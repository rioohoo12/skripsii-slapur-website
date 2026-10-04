<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div>
        <p class="hero-kicker">Portal Guru</p>
        <h2>Selamat datang, {{ user?.name || 'Guru' }}</h2>
        <p>Ringkasan data, jadwal mengajar, pengumuman sekolah, dan aktivitas terbaru dalam satu halaman.</p>
      </div>
      <router-link to="/guru/jadwal" class="hero-action">Lihat Jadwal Hari Ini</router-link>
    </section>

    <section>
      <h3 class="section-title">Ringkasan Data</h3>
      <div class="stats-grid">
        <article v-for="item in statCards" :key="item.label" class="stat-card">
          <p class="stat-label">{{ item.label }}</p>
          <p class="stat-value">{{ item.value }}</p>
          <p class="stat-sub">{{ item.sub }}</p>
        </article>
      </div>
    </section>

    <section class="content-grid">
      <article class="panel-card">
        <div class="panel-head">
          <h3>Jadwal Mengajar Hari Ini</h3>
          <router-link to="/guru/jadwal" class="panel-link">Lihat Semua</router-link>
        </div>
        <div class="timeline">
          <div v-for="row in todaySchedule" :key="row.time + row.className" class="timeline-item">
            <p class="time">{{ row.time }}</p>
            <div>
              <p class="title">{{ row.subject }}</p>
              <p class="meta">Kelas {{ row.className }}</p>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card">
        <div class="panel-head">
          <h3>Aktivitas Terbaru</h3>
        </div>
        <ul class="activity-list">
          <li v-for="item in activities" :key="item.text">
            <p class="title">{{ item.text }}</p>
            <p class="meta">{{ item.time }}</p>
          </li>
        </ul>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const statCards = computed(() => {
  const jenjang = user.value?.jenjang_guru;
  let numClasses = 0;
  if (jenjang === 'smp' || jenjang === 'sma') {
    numClasses = 3;
  } else if (jenjang === 'smp_sma') {
    numClasses = 6;
  }

  return [
    { label: 'Jumlah Kelas yang Diampu', value: numClasses.toString(), sub: 'Sesuai jenjang mengajar Anda' },
    { label: 'Jumlah Siswa yang Diajar', value: '192', sub: 'Estimasi total siswa aktif semester ini' },
  ];
});

const todaySchedule = computed(() => {
  const u = user.value;
  const subjSmp = u.subject_smp_name || 'Mata Pelajaran SMP';
  const subjSma = u.subject_sma_name || 'Mata Pelajaran SMA';
  
  const timeSlots = [
    '07:30 - 09:00',
    '09:15 - 10:45',
    '11:00 - 12:30',
    '13:15 - 14:45',
    '15:00 - 16:30'
  ];

  const classesToSchedule = [];
  
  if (u.jenjang_guru === 'smp' || u.jenjang_guru === 'smp_sma') {
    classesToSchedule.push({ className: 'VII A', subject: subjSmp });
    classesToSchedule.push({ className: 'VII B', subject: subjSmp });
    if (u.jenjang_guru === 'smp') {
      classesToSchedule.push({ className: 'VIII A', subject: subjSmp });
      classesToSchedule.push({ className: 'VIII B', subject: subjSmp });
    }
  }
  
  if (u.jenjang_guru === 'sma' || u.jenjang_guru === 'smp_sma') {
    classesToSchedule.push({ className: 'X IPA 1', subject: subjSma });
    classesToSchedule.push({ className: 'X IPA 2', subject: subjSma });
    if (u.jenjang_guru === 'smp_sma') {
      classesToSchedule.push({ className: 'XI IPA 1', subject: subjSma });
    } else {
      classesToSchedule.push({ className: 'XI IPA 1', subject: subjSma });
      classesToSchedule.push({ className: 'XI IPA 2', subject: subjSma });
    }
  }

  if (classesToSchedule.length === 0) {
    classesToSchedule.push({ className: 'VII A', subject: 'Matematika' });
    classesToSchedule.push({ className: 'VIII A', subject: 'Matematika' });
  }

  const today = [];
  for (let i = 0; i < classesToSchedule.length && i < timeSlots.length; i++) {
    today.push({
      time: timeSlots[i],
      subject: classesToSchedule[i].subject,
      className: classesToSchedule[i].className
    });
  }
  
  return today;
});

const announcements = [
  { title: 'Informasi dari admin: pembaruan kalender akademik', date: 'Hari ini, 08:15' },
  { title: 'Jadwal rapat guru bulanan pada Jumat 14:00', date: 'Kemarin, 15:40' },
  { title: 'Kegiatan sekolah terbaru: Pekan Literasi Digital', date: '20 Feb 2026, 09:00' },
];

const activities = [
  { text: '3 siswa baru ditambahkan ke kelas X IPA 2', time: 'Hari ini, 09:20' },
  { text: 'Laporan rekap nilai XI IPA 1 baru dibuat', time: 'Hari ini, 08:05' },
  { text: 'Survei mutu pembelajaran semester genap baru diisi', time: 'Kemarin, 16:40' },
];
</script>

<style scoped>
.dashboard-page {
  display: grid;
  gap: 1rem;
}
.section-title {
  margin: 0 0 0.7rem;
  color: #1e293b;
  font-size: 1rem;
}
.hero-card {
  background: linear-gradient(120deg, #1e3a8a 0%, #2563eb 100%);
  color: #fff;
  border-radius: 16px;
  padding: 1.3rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.hero-kicker {
  font-size: 0.8rem;
  opacity: 0.85;
  margin: 0 0 0.35rem;
}
.hero-card h2 {
  margin: 0;
  font-size: 1.3rem;
}
.hero-card p {
  margin: 0.5rem 0 0;
  opacity: 0.9;
}
.hero-action {
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  text-decoration: none;
  border: 1px solid rgba(255, 255, 255, 0.35);
  padding: 0.55rem 0.85rem;
  border-radius: 10px;
  font-size: 0.85rem;
  white-space: nowrap;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}
.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}
.stat-label {
  color: #64748b;
  font-size: 0.8rem;
  margin: 0;
}
.stat-value {
  color: #0f172a;
  font-weight: 800;
  font-size: 1.5rem;
  margin: 0.2rem 0;
}
.stat-sub {
  color: #94a3b8;
  font-size: 0.8rem;
  margin: 0;
}
.content-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 0.9rem;
}
.panel-card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 1rem;
}
.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.7rem;
}
.panel-head h3 {
  margin: 0;
  font-size: 1rem;
  color: #1e293b;
}
.panel-link {
  color: #2563eb;
  text-decoration: none;
  font-size: 0.83rem;
  font-weight: 600;
}
.timeline {
  display: grid;
  gap: 0.6rem;
}
.timeline-item {
  display: flex;
  gap: 0.8rem;
  padding: 0.65rem 0.7rem;
  border-radius: 10px;
  background: #f8fafc;
}
.timeline-item .time {
  margin: 0;
  font-size: 0.78rem;
  color: #334155;
  font-weight: 700;
  min-width: 100px;
}
.timeline-item .title,
.announce-list .title {
  margin: 0;
  color: #1e293b;
  font-size: 0.9rem;
  font-weight: 600;
}
.timeline-item .meta,
.announce-list .meta {
  margin: 0.2rem 0 0;
  color: #64748b;
  font-size: 0.78rem;
}
.announce-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.6rem;
}
.announce-list li,
.activity-list li {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.65rem 0.7rem;
}
.activity-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.6rem;
}
.quick-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.6rem;
}
.quick-link {
  text-decoration: none;
  color: #1e293b;
  border: 1px solid #dbeafe;
  background: #f8fbff;
  border-radius: 10px;
  padding: 0.7rem;
  font-size: 0.86rem;
  font-weight: 600;
}
.quick-link:hover {
  background: #eff6ff;
}
@media (max-width: 1100px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .content-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .hero-action {
    width: 100%;
    text-align: center;
  }
  .timeline-item {
    flex-direction: column;
    gap: 0.35rem;
  }
  .timeline-item .time {
    min-width: auto;
  }
  .quick-grid {
    grid-template-columns: 1fr;
  }
}
</style>
