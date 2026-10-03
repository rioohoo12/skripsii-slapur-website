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
import { computed } from 'vue';

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const schedule = computed(() => {
  const u = user.value;
  const subjSmp = u.subject_smp_name || 'Mata Pelajaran SMP';
  const subjSma = u.subject_sma_name || 'Mata Pelajaran SMA';
  
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  const timeSlots = [
    '07:30 - 09:00',
    '09:15 - 10:45',
    '11:00 - 12:30',
    '13:15 - 14:45',
    '15:00 - 16:30'
  ];

  const classesToSchedule = [];
  
  // Prepare list of classes to teach
  if (u.jenjang_guru === 'smp' || u.jenjang_guru === 'smp_sma') {
    const smpClasses = ['VII A', 'VII B', 'VIII A', 'VIII B', 'IX A', 'IX B'];
    smpClasses.forEach(c => {
      classesToSchedule.push({ className: c, subject: subjSmp });
      classesToSchedule.push({ className: c, subject: subjSmp }); // 2x per minggu
    });
  }
  
  if (u.jenjang_guru === 'sma' || u.jenjang_guru === 'smp_sma') {
    const smaClasses = ['X IPA 1', 'X IPA 2', 'XI IPA 1', 'XI IPA 2', 'XII IPA 1', 'XII IPA 2'];
    smaClasses.forEach(c => {
      classesToSchedule.push({ className: c, subject: subjSma });
      if (u.jenjang_guru === 'sma') {
        classesToSchedule.push({ className: c, subject: subjSma }); // 2x per minggu jika hanya SMA
      }
    });
  }

  if (classesToSchedule.length === 0) {
    classesToSchedule.push({ className: 'VII A', subject: 'Matematika' });
    classesToSchedule.push({ className: 'VIII A', subject: 'Matematika' });
  }

  const result = [];
  let slotIndex = 0;

  days.forEach(day => {
    const dayItems = [];
    timeSlots.forEach(time => {
      if (slotIndex < classesToSchedule.length) {
        const item = classesToSchedule[slotIndex];
        const room = item.className.includes('VII') ? 'R-101' : 
                     item.className.includes('VIII') ? 'R-102' :
                     item.className.includes('IX') ? 'R-103' :
                     item.className.includes('X') ? 'LAB-1' :
                     item.className.includes('XI') ? 'LAB-2' : 'LAB-3';
                     
        dayItems.push({
          time: time,
          subject: item.subject,
          className: item.className,
          room: room,
          status: day === 'Senin' ? 'Selesai' : (day === 'Selasa' ? 'Berikutnya' : 'Terjadwal')
        });
        slotIndex++;
      }
    });
    if (dayItems.length > 0) {
      result.push({ name: day, items: dayItems });
    }
  });

  return result;
});
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
