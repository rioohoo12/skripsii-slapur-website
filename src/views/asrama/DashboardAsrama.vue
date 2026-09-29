<template>
  <div class="dashboard-asrama">
    <div class="welcome-card">
      <div class="welcome-content">
        <h1>Selamat Datang, Staff Asrama!</h1>
        <p>Kelola data penghuni, kapasitas kamar, dan fasilitas asrama dari dashboard ini.</p>
      </div>
      <div class="welcome-img">🏰</div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon bg-purple">🛏️</div>
        <div class="stat-info">
          <span class="stat-value">{{ loading ? '...' : stats.total_kamar }}</span>
          <span class="stat-label">Total Kamar</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-blue">👥</div>
        <div class="stat-info">
          <span class="stat-value">{{ loading ? '...' : stats.total_penghuni }}</span>
          <span class="stat-label">Total Penghuni</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-green">✨</div>
        <div class="stat-info">
          <span class="stat-value">{{ loading ? '...' : stats.kamar_kosong }}</span>
          <span class="stat-label">Kamar Kosong</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-orange">⚠️</div>
        <div class="stat-info">
          <span class="stat-value">{{ loading ? '...' : stats.permintaan_pindah }}</span>
          <span class="stat-label">Permintaan Pindah</span>
        </div>
      </div>
    </div>

    <div class="content-cards">
      <div class="content-card">
        <h3>Aktivitas Terbaru</h3>
        <p class="placeholder-text">Belum ada aktivitas terbaru hari ini.</p>
      </div>
      <div class="content-card">
        <h3>Kapasitas Gedung</h3>
        <p v-if="loading" class="placeholder-text">Memuat data...</p>
        <ul v-else-if="stats.kapasitas_gedung.length > 0" class="building-list">
          <li v-for="(g, i) in stats.kapasitas_gedung" :key="i">
            <span>{{ g.gedung }}</span> 
            <strong :class="g.occupancy >= 90 ? 'text-danger' : (g.occupancy >= 70 ? 'text-warning' : 'text-success')">
              {{ g.occupancy }}% Terisi
            </strong>
          </li>
        </ul>
        <p v-else class="placeholder-text">Belum ada data gedung.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stats = ref({
  total_kamar: 0,
  total_penghuni: 0,
  kamar_kosong: 0,
  permintaan_pindah: 0,
  kapasitas_gedung: []
});

const loading = ref(true);

onMounted(async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/staff/asrama/dashboard', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      stats.value = await res.json();
    }
  } catch (e) {
    console.error('Failed to load asrama stats:', e);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.dashboard-asrama {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.welcome-card {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  border-radius: 16px;
  padding: 2.5rem;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 25px rgba(109, 40, 217, 0.2);
}

.welcome-content h1 {
  margin: 0 0 0.5rem 0;
  font-size: 1.8rem;
  font-weight: 700;
}

.welcome-content p {
  margin: 0;
  font-size: 1.05rem;
  opacity: 0.9;
}

.welcome-img {
  font-size: 5rem;
  opacity: 0.8;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.bg-purple { background: #f3e8ff; color: #7e22ce; }
.bg-blue { background: #dbeafe; color: #1d4ed8; }
.bg-green { background: #dcfce7; color: #15803d; }
.bg-orange { background: #ffedd5; color: #c2410c; }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.content-cards {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.25rem;
}

.content-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
  border: 1px solid #f1f5f9;
}

.content-card h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  color: #1e293b;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 0.75rem;
}

.placeholder-text {
  color: #94a3b8;
  font-style: italic;
}

.building-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.building-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  font-size: 0.9rem;
}

.building-list li strong {
  color: #8b5cf6;
}

@media (max-width: 768px) {
  .welcome-card {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }
  .content-cards {
    grid-template-columns: 1fr;
  }
}
</style>
