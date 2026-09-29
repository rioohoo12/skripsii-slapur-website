<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard Kafetaria</h1>
        <p class="page-subtitle">Ringkasan operasional kafetaria hari ini</p>
      </div>
    </div>

    <div class="dashboard-grid">
      <!-- Summary Cards -->
      <div class="stat-card">
        <div class="stat-icon morning">🌅</div>
        <div class="stat-content">
          <p class="stat-label">Sarapan Pagi</p>
          <h3 class="stat-value">{{ summary.Pagi }} <span class="stat-unit">Siswa</span></h3>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon noon">☀️</div>
        <div class="stat-content">
          <p class="stat-label">Makan Siang</p>
          <h3 class="stat-value">{{ summary.Siang }} <span class="stat-unit">Siswa</span></h3>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon evening">🌙</div>
        <div class="stat-content">
          <p class="stat-label">Makan Sore</p>
          <h3 class="stat-value">{{ summary.Sore }} <span class="stat-unit">Siswa</span></h3>
        </div>
      </div>

      <div class="stat-card total">
        <div class="stat-icon all">🍽️</div>
        <div class="stat-content">
          <p class="stat-label">Total Kehadiran</p>
          <h3 class="stat-value">{{ summary.Total }} <span class="stat-unit">Siswa</span></h3>
        </div>
      </div>

      <!-- Menu Hari Ini -->
      <div class="content-card menu-card">
        <div class="card-header">
          <h2>Menu Hari Ini</h2>
          <span class="badge date-badge">{{ todayFormatted }}</span>
        </div>
        
        <div v-if="loading" class="loading-state">
          Memuat data...
        </div>
        <div v-else-if="menus.length === 0" class="empty-state">
          Belum ada menu yang diatur untuk hari ini.
        </div>
        <div v-else class="menu-list">
          <div v-for="menu in menus" :key="menu.id" class="menu-item">
            <div class="menu-time">
              <span class="time-dot" :class="menu.meal_time.toLowerCase()"></span>
              {{ menu.meal_time }}
            </div>
            <div class="menu-details">
              {{ menu.menu_details }}
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="content-card actions-card">
        <div class="card-header">
          <h2>Aksi Cepat</h2>
        </div>
        <div class="actions-grid">
          <router-link to="/kafetaria/scanner" class="action-btn primary">
            <span class="action-icon">📝</span>
            <span>Input Presensi Makan</span>
          </router-link>
          
          <router-link to="/kafetaria/menus" class="action-btn secondary">
            <span class="action-icon">🍲</span>
            <span>Kelola Menu</span>
          </router-link>
          
          <router-link to="/kafetaria/laporan" class="action-btn secondary">
            <span class="action-icon">📈</span>
            <span>Lihat Laporan</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { request } from '@/api/auth';

const summary = ref({ Pagi: 0, Siang: 0, Sore: 0, Total: 0 });
const menus = ref([]);
const loading = ref(true);

const todayFormatted = new Date().toLocaleDateString('id-ID', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});

async function fetchData() {
  loading.value = true;
  try {
    const res = await request('/kafetaria/dashboard');
    summary.value = res.summary || { Pagi: 0, Siang: 0, Sore: 0, Total: 0 };
    menus.value = res.menus || [];
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchData();
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

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.stat-card.total {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.stat-card.total .stat-label,
.stat-card.total .stat-unit {
  color: rgba(255, 255, 255, 0.8);
}

.stat-card.total .stat-value {
  color: white;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon.morning { background: #e0f2fe; color: #0284c7; }
.stat-icon.noon { background: #fef3c7; color: #d97706; }
.stat-icon.evening { background: #ede9fe; color: #7c3aed; }
.stat-icon.all { background: rgba(255, 255, 255, 0.2); }

.stat-content {
  flex: 1;
}

.stat-label {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  color: #64748b;
}

.stat-value {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}

.stat-unit {
  font-size: 0.875rem;
  font-weight: 500;
  color: #94a3b8;
}

.content-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.card-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.badge {
  background: #f1f5f9;
  color: #475569;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.menu-card {
  grid-column: span 3;
}

.actions-card {
  grid-column: span 1;
}

.menu-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.menu-item {
  display: flex;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
}

.menu-time {
  width: 120px;
  font-weight: 600;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.time-dot.pagi { background-color: #38bdf8; }
.time-dot.siang { background-color: #fbbf24; }
.time-dot.sore { background-color: #8b5cf6; }

.menu-details {
  flex: 1;
  color: #475569;
  white-space: pre-wrap;
}

.actions-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn.primary {
  background: #f59e0b;
  color: white;
}

.action-btn.primary:hover {
  background: #d97706;
}

.action-btn.secondary {
  background: #f1f5f9;
  color: #475569;
}

.action-btn.secondary:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.action-icon {
  font-size: 1.25rem;
}

.empty-state, .loading-state {
  text-align: center;
  padding: 2rem;
  color: #64748b;
  background: #f8fafc;
  border-radius: 12px;
}
</style>
