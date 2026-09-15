<template>
  <div class="dining-page">
    <!-- Header Card -->
    <div class="header-card">
      <div class="header-info">
        <span class="badge-tag">SISTEM DINING ASRAMA</span>
        <h2>Laporan & Riwayat Presensi Makan</h2>
        <p>Catatan keikutsertaan makan murid di kantin yang diinput langsung oleh Staff Dining.</p>
      </div>

      <div class="dining-number-card">
        <div class="label">NOMOR MAKAN ANDA</div>
        <div class="number-display">#{{ diningData.dining_number || '001' }}</div>
        <div class="sub-info">
          <span>Meja: <strong>{{ diningData.table_number || 'Meja #001' }}</strong></span>
          <span class="status-badge active">{{ diningData.dining_status || 'Aktif' }}</span>
        </div>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="metrics-grid">
      <div class="metric-card primary">
        <div class="metric-icon">🍽️</div>
        <div class="metric-content">
          <div class="metric-label">Total Presensi Makan</div>
          <div class="metric-value">{{ diningData.summary?.total_makan || 0 }} Kali</div>
        </div>
      </div>

      <div class="metric-card warning">
        <div class="metric-icon">🌅</div>
        <div class="metric-content">
          <div class="metric-label">Makan Pagi</div>
          <div class="metric-value">{{ diningData.summary?.pagi || 0 }} Kali</div>
        </div>
      </div>

      <div class="metric-card success">
        <div class="metric-icon">☀️</div>
        <div class="metric-content">
          <div class="metric-label">Makan Siang</div>
          <div class="metric-value">{{ diningData.summary?.siang || 0 }} Kali</div>
        </div>
      </div>

      <div class="metric-card info">
        <div class="metric-icon">🌙</div>
        <div class="metric-content">
          <div class="metric-label">Makan Sore / Malam</div>
          <div class="metric-value">{{ diningData.summary?.sore || 0 }} Kali</div>
        </div>
      </div>
    </div>

    <!-- Laporan Makan di Dining Table -->
    <div class="table-container">
      <div class="table-header">
        <div>
          <h3>Laporan Makan di Dining</h3>
          <p class="table-sub">Data realtime presensi makan siswa yang diinput oleh Staff Dining.</p>
        </div>
        <button class="refresh-btn" @click="fetchDiningInfo" :disabled="loading">
          <span class="spin-icon" :class="{ spinning: loading }">🔄</span> Perbarui Data
        </button>
      </div>

      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 70px;">No</th>
              <th>Tanggal</th>
              <th>Waktu</th>
              <th>Jenis Makan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="text-center py-4">Memuat data Laporan Makan...</td>
            </tr>
            <tr v-else-if="!diningData.laporan_makan || diningData.laporan_makan.length === 0">
              <td colspan="4" class="empty-state">
                <div class="empty-icon">🍽️</div>
                <div class="empty-title">Belum ada riwayat makan</div>
                <div class="empty-text">Saat Anda makan di kantin, Staff Dining akan menginput nomor makan <strong>#{{ diningData.dining_number || '001' }}</strong> dan catatannya akan otomatis muncul di sini.</div>
              </td>
            </tr>
            <tr v-else v-for="item in diningData.laporan_makan" :key="item.id">
              <td class="font-bold">{{ item.no }}</td>
              <td>
                <span class="date-badge">📅 {{ item.tanggal }}</span>
              </td>
              <td>
                <span class="time-badge">⏰ {{ item.waktu }}</span>
              </td>
              <td>
                <span :class="['meal-badge', getMealTypeClass(item.jenis_makan)]">
                  {{ getMealTypeIcon(item.jenis_makan) }} {{ item.jenis_makan }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const loading = ref(false);
let timer = null;

const diningData = ref({
  dining_number: '001',
  table_number: 'Meja #001',
  dining_status: 'active',
  summary: {
    total_makan: 0,
    pagi: 0,
    siang: 0,
    sore: 0,
  },
  laporan_makan: [],
});

function getMealTypeClass(type) {
  const t = (type || '').toLowerCase();
  if (t === 'pagi') return 'pagi';
  if (t === 'siang') return 'siang';
  return 'sore';
}

function getMealTypeIcon(type) {
  const t = (type || '').toLowerCase();
  if (t === 'pagi') return '🌅';
  if (t === 'siang') return '☀️';
  return '🌙';
}

async function fetchDiningInfo() {
  loading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    const res = await fetch('/api/dining/info', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      const data = await res.json();
      diningData.value = data;
    }
  } catch (e) {
    console.error('Failed to fetch dining info:', e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchDiningInfo();
  // Auto refresh every 10 seconds to catch staff inputs live
  timer = setInterval(fetchDiningInfo, 10000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<style scoped>
.dining-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.header-card {
  background: linear-gradient(135deg, #0f1e3c 0%, #1e3a8a 100%);
  color: #ffffff;
  padding: 2.2rem;
  border-radius: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 10px 25px rgba(15, 30, 60, 0.15);
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-info {
  max-width: 550px;
}

.badge-tag {
  background: rgba(255, 255, 255, 0.15);
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.header-info h2 {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0.6rem 0 0.4rem;
  color: #ffffff;
}

.header-info p {
  color: #93c5fd;
  font-size: 0.95rem;
  margin: 0;
}

.dining-number-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem 2rem;
  border-radius: 16px;
  text-align: center;
  min-width: 220px;
}

.dining-number-card .label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #bfdbfe;
  letter-spacing: 1px;
}

.number-display {
  font-size: 2.75rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0.2rem 0;
  letter-spacing: 2px;
  text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.sub-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 0.85rem;
  color: #e0f2fe;
}

.status-badge {
  background: #10b981;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
}

/* Metrics */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
}

.metric-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 1.25rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.metric-icon {
  font-size: 1.8rem;
  width: 50px;
  height: 50px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
}

.metric-card.primary .metric-icon { background: #eff6ff; }
.metric-card.warning .metric-icon { background: #fffbebfb; }
.metric-card.success .metric-icon { background: #ecfdf5; }
.metric-card.info .metric-icon { background: #f0f9ff; }

.metric-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

.metric-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #1e293b;
  margin-top: 0.2rem;
}

/* Table */
.table-container {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-center;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.table-header h3 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem;
}

.table-sub {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
}

.refresh-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #334155;
  padding: 0.55rem 1rem;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.refresh-btn:hover {
  background: #e2e8f0;
  color: #0f1e3c;
}

.spin-icon.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.data-table th {
  background: #f8fafc;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.85rem 1rem;
  border-bottom: 2px solid #e2e8f0;
}

.data-table td {
  padding: 1rem;
  font-size: 0.9rem;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.date-badge {
  font-weight: 600;
  color: #1e293b;
}

.time-badge {
  font-family: monospace;
  font-size: 0.85rem;
  color: #475569;
  background: #f1f5f9;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
}

.meal-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.8rem;
}

.meal-badge.pagi {
  background: #fffbebfb;
  color: #b45309;
  border: 1px solid #fde68a;
}

.meal-badge.siang {
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.meal-badge.sore {
  background: #f0f9ff;
  color: #0369a1;
  border: 1px solid #bae6fd;
}

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem !important;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 0.75rem;
}

.empty-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #334155;
}

.empty-text {
  font-size: 0.9rem;
  color: #64748b;
  max-width: 450px;
  margin: 0.4rem auto 0;
}
</style>
