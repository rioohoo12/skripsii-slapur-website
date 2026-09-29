<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Layanan Kafetaria / Dining</h1>
      <p class="page-subtitle">Lihat menu hari ini dan tunjukkan QR Code saat mengambil makanan</p>
    </div>

    <div class="layout-grid">
      <!-- QR Code Section -->
      <div class="card qr-card">
        <h3>Identitas Dining</h3>
        
        <div v-if="loading" class="text-center loading">
          Memuat data...
        </div>
        <div v-else-if="!diningNumber" class="text-center empty">
          Anda belum memiliki akses layanan dining.
        </div>
        <div v-else class="qr-container">
          <div class="qr-box">
            <qrcode-vue :value="diningNumber" :size="200" level="M" />
          </div>
          
          <div class="student-info">
            <h4>{{ studentName }}</h4>
            <p class="dining-id">ID: {{ diningNumber }}</p>
          </div>
          
          <div class="instructions">
            <p>Tunjukkan QR Code ini kepada Staff Kafetaria saat mengambil jatah makan Anda.</p>
          </div>
        </div>
      </div>

      <!-- Menu Section -->
      <div class="card menu-card">
        <h3>Menu Hari Ini <span>({{ todayFormatted }})</span></h3>
        
        <div v-if="loading" class="text-center loading">
          Memuat menu...
        </div>
        <div v-else-if="menus.length === 0" class="text-center empty">
          Tidak ada data menu untuk hari ini.
        </div>
        <div v-else class="menu-list">
          <div 
            v-for="menu in menus" 
            :key="menu.id" 
            class="menu-item"
            :class="{ 'consumed': isConsumed(menu.meal_time) }"
          >
            <div class="menu-header">
              <div class="menu-time">
                <span class="time-dot" :class="menu.meal_time.toLowerCase()"></span>
                {{ menu.meal_time }}
              </div>
              <span class="status-badge" v-if="isConsumed(menu.meal_time)">
                ✅ Sudah Diambil
              </span>
              <span class="status-badge pending" v-else>
                ⏳ Belum Diambil
              </span>
            </div>
            <div class="menu-details">
              {{ menu.menu_details }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { request } from '@/api/auth';
import QrcodeVue from 'qrcode.vue';

const menus = ref([]);
const consumed = ref([]);
const diningNumber = ref('');
const studentName = ref('');
const loading = ref(true);

const todayFormatted = new Date().toLocaleDateString('id-ID', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});

function isConsumed(mealTime) {
  return consumed.value.includes(mealTime);
}

async function fetchData() {
  loading.value = true;
  try {
    const res = await request('/kafetaria/today');
    menus.value = res.menus || [];
    consumed.value = res.consumed || [];
    diningNumber.value = res.dining_number || '';
    studentName.value = res.student_name || '';
  } catch (error) {
    console.error('Failed to fetch kafetaria data', error);
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
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.page-header {
  margin-bottom: 1rem;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0;
}

.layout-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 1.5rem;
  align-items: start;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.card h3 {
  margin: 0 0 1.5rem 0;
  color: #1e293b;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.card h3 span { font-size: 0.9rem; color: #64748b; font-weight: normal; }

.qr-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.qr-box {
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.student-info {
  text-align: center;
  margin-bottom: 1.5rem;
}

.student-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
  color: #1e293b;
}

.dining-id {
  margin: 0;
  color: #64748b;
  font-family: monospace;
  font-size: 1.1rem;
}

.instructions {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  color: #475569;
  font-size: 0.9rem;
  border: 1px solid #e2e8f0;
}

.menu-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.menu-item {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  background: #f8fafc;
  transition: all 0.2s;
}

.menu-item.consumed {
  opacity: 0.7;
  background: #f0fdf4;
  border-color: #bbf7d0;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
}
.menu-item.consumed .menu-header { border-color: #bbf7d0; }

.menu-time {
  font-weight: 600;
  color: #1e293b;
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

.status-badge {
  font-size: 0.8rem;
  font-weight: 600;
  color: #15803d;
}
.status-badge.pending { color: #d97706; }

.menu-details {
  color: #475569;
  white-space: pre-wrap;
  line-height: 1.5;
}

.text-center { text-align: center; }
.loading, .empty { padding: 2rem; color: #64748b; }

@media (max-width: 900px) {
  .layout-grid { grid-template-columns: 1fr; }
}
</style>
