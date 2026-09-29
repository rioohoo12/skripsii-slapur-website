<template>
  <div class="jadwal-page" :class="themeClass">
    <PageCard :jenis-kelamin="jenisKelamin">
      <template #header>Jadwal Pelajaran</template>
      
      <div class="schedule-grid">
        <div v-for="(daySchedule, dayName) in schedule" :key="dayName" class="day-card">
          <div class="day-header" :class="{ 'weekend': isWeekend(dayName) }">
            {{ dayName }}
          </div>
          <div class="day-content">
            <template v-if="daySchedule.length > 0">
              <div v-for="(cls, idx) in daySchedule" :key="idx" class="class-item">
                <span class="class-time">⏰ {{ cls.time }}</span>
                <span class="class-subject">{{ cls.subject }}</span>
              </div>
            </template>
            <template v-else>
              <div class="no-class">Libur / Tidak ada jadwal</div>
            </template>
          </div>
        </div>
      </div>
    </PageCard>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({
  jenisKelamin: { type: String, default: 'laki-laki' }
});

const themeClass = computed(() => 'theme-' + props.jenisKelamin);
const isWeekend = (day) => day === 'Sabtu' || day === 'Minggu';

const schedule = ref({
  Senin: [
    { time: '08:00 - 10:00', subject: 'Biologi' },
    { time: '10:15 - 11:45', subject: 'Matematika' },
    { time: '12:30 - 14:00', subject: 'Bahasa Indonesia' },
  ],
  Selasa: [
    { time: '08:00 - 09:30', subject: 'Fisika' },
    { time: '09:45 - 11:15', subject: 'Bahasa Inggris' },
    { time: '12:00 - 13:30', subject: 'Pendidikan Agama' },
  ],
  Rabu: [
    { time: '08:00 - 10:00', subject: 'Kimia' },
    { time: '10:15 - 11:45', subject: 'Sejarah' },
    { time: '12:30 - 14:00', subject: 'Penjaskes' },
  ],
  Kamis: [
    { time: '08:00 - 09:30', subject: 'Ekonomi' },
    { time: '09:45 - 11:15', subject: 'Seni Budaya' },
    { time: '12:00 - 13:30', subject: 'Matematika' },
  ],
  Jumat: [
    { time: '08:00 - 09:30', subject: 'Bahasa Inggris' },
    { time: '09:45 - 11:15', subject: 'Biologi' },
  ],
  Sabtu: [],
  Minggu: []
});
</script>

<style scoped>
.jadwal-page {
  display: flex;
  flex-direction: column;
}

.schedule-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.day-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
}

.day-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.day-header {
  background: #0f766e; /* Default Laki-laki primary color */
  color: white;
  padding: 0.85rem 1rem;
  font-weight: 600;
  font-size: 1.1rem;
  text-align: center;
  letter-spacing: 0.5px;
}

.theme-perempuan .day-header {
  background: #7c3aed; /* Perempuan primary color */
}

.day-header.weekend {
  background: #ef4444 !important; /* Libur color */
}

.day-content {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex: 1;
  background: #f8fafc;
}

.class-item {
  display: flex;
  flex-direction: column;
  padding: 0.85rem;
  background: #ffffff;
  border-left: 4px solid #0f766e;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.theme-perempuan .class-item {
  border-left-color: #7c3aed;
}

.class-time {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.35rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.class-subject {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1e293b;
}

.no-class {
  text-align: center;
  color: #94a3b8;
  font-style: italic;
  padding: 2.5rem 0;
  font-weight: 500;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.no-class::before {
  content: '😴';
  font-size: 2rem;
}
</style>
