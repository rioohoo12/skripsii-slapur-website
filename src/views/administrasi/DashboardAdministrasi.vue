<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div>
        <p class="hero-kicker">Portal Administrasi</p>
        <h2>Selamat datang, {{ user?.name || 'Staff Administrasi' }}</h2>
        <p>Ringkasan pendaftaran siswa baru, status verifikasi dokumen, dan aktivitas terbaru dalam satu halaman.</p>
      </div>
      <router-link to="/administrasi/dokumen" class="hero-action">Kelola Dokumen Masuk</router-link>
    </section>

    <section>
      <h3 class="section-title">Ringkasan Data</h3>
      <div class="stats-grid">
        <article class="stat-card">
          <p class="stat-label">Dokumen Menunggu Verifikasi</p>
          <p class="stat-value">{{ stats.waiting_docs }}</p>
          <p class="stat-sub">Butuh tindakan Anda hari ini</p>
        </article>
        <article class="stat-card">
          <p class="stat-label">Pendaftar Baru</p>
          <p class="stat-value">{{ stats.recent_applicants }}</p>
          <p class="stat-sub">Siswa mendaftar minggu ini</p>
        </article>
      </div>
    </section>

    <section class="content-grid">
      <article class="panel-card">
        <div class="panel-head">
          <h3>Dokumen Masuk Hari Ini</h3>
          <router-link to="/administrasi/dokumen" class="panel-link">Lihat Semua</router-link>
        </div>
        <div class="timeline">
          <div v-for="row in recentDocs" :key="row.id" class="timeline-item">
            <p class="time">{{ row.time }}</p>
            <div>
              <p class="title">{{ row.type }}</p>
              <p class="meta">Oleh: {{ row.student }}</p>
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
import { ref, onMounted, computed } from 'vue';
import { authApi } from '@/api/auth';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const stats = ref({
  waiting_docs: 0,
  recent_applicants: 0
});

const recentDocs = ref([
  { id: 1, time: '09:15 - 09:30', type: 'Verifikasi Ijazah SMP', student: 'Budi Santoso' },
  { id: 2, time: '09:45 - 10:00', type: 'Verifikasi Kartu Keluarga', student: 'Siti Aminah' },
  { id: 3, time: '11:10 - 11:30', type: 'Pengecekan Akte Kelahiran', student: 'Ahmad Faisal' },
  { id: 4, time: '13:00 - 13:20', type: 'Surat Pindah', student: 'Dewi Lestari' }
]);

const activities = [
  { text: 'Pembayaran pendaftaran Siti Aminah telah dikonfirmasi', time: 'Hari ini, 09:20' },
  { text: 'Budi Santoso melengkapi formulir data diri', time: 'Hari ini, 08:05' },
  { text: 'Dokumen Akte Kelahiran atas nama Ahmad Faisal ditolak', time: 'Kemarin, 16:40' },
];

const fetchDashboard = async () => {
  try {
    // Sesuaikan dengan rute API baru atau abaikan jika belum ada rute spesifik 
    // const res = await authApi.fetch('/staff/dashboard');
    // stats.value = res.stats || res.data?.stats || stats.value;
    
    // Dummy
    stats.value = {
      waiting_docs: 14,
      recent_applicants: 32
    };
  } catch (error) {
    console.error("Gagal mengambil data dashboard", error);
  }
};

onMounted(() => {
  fetchDashboard();
});
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
