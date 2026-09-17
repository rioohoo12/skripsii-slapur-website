<template>
  <div class="staff-dashboard">
    <header class="staff-topbar">
      <div class="brand">
        <span class="logo">🍽️</span>
        <h1 class="staff-title">Dashboard Staff Dining</h1>
      </div>
      <div class="staff-user">
        <span class="staff-name">👤 {{ user?.name || 'Staff Dining' }}</span>
        <button type="button" class="btn-logout" @click="handleLogout">Logout</button>
      </div>
    </header>

    <main class="staff-content">
      <!-- Input Card -->
      <div class="dining-input-card">
        <div class="card-header">
          <h2>Input Presensi Dining (Nomor Makan Siswa)</h2>
          <p>Ketik nomor urut makan siswa lalu tekan <strong>Enter</strong> atau klik <strong>Simpan Presensi</strong>.</p>
        </div>

        <form @submit.prevent="submitMealInput" class="input-form">
          <div class="form-group">
            <label for="dining-num">NOMOR MAKAN SISWA</label>
            <div class="input-wrapper">
              <span class="prefix">#</span>
              <input
                id="dining-num"
                ref="diningInputRef"
                v-model="diningNumber"
                type="text"
                placeholder="Contoh: 001"
                required
                autocomplete="off"
                :disabled="submitting"
              />
            </div>
          </div>

          <div class="form-group">
            <label>JENIS MAKAN</label>
            <div class="meal-options">
              <label :class="['meal-radio', { active: mealTime === 'pagi' }]">
                <input type="radio" v-model="mealTime" value="pagi" />
                <span>🌅 Pagi</span>
              </label>
              <label :class="['meal-radio', { active: mealTime === 'siang' }]">
                <input type="radio" v-model="mealTime" value="siang" />
                <span>☀️ Siang</span>
              </label>
              <label :class="['meal-radio', { active: mealTime === 'sore' }]">
                <input type="radio" v-model="mealTime" value="sore" />
                <span>🌙 Sore</span>
              </label>
            </div>
          </div>

          <button type="submit" class="submit-btn" :disabled="submitting || !diningNumber">
            <span v-if="submitting">Menyimpan...</span>
            <span v-else>✔ Simpan Presensi Makan (Enter)</span>
          </button>
        </form>

        <!-- Feedback Messages -->
        <Transition name="fade">
          <div v-if="feedback" :class="['feedback-alert', feedback.type]">
            <span class="icon">{{ feedback.type === 'success' ? '✅' : '❌' }}</span>
            <span>{{ feedback.message }}</span>
          </div>
        </Transition>
      </div>

      <!-- Today Logs Table -->
      <div class="today-logs-card">
        <div class="table-header">
          <div>
            <h3>Daftar Presensi Makan Hari Ini ({{ todayLogs.today || 'Hari ini' }})</h3>
            <p>Total diinput: <strong>{{ todayLogs.total_input_today || 0 }} Kali Makan</strong></p>
          </div>
          <button class="refresh-btn" @click="fetchTodayLogs">🔄 Refresh</button>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nomor Makan</th>
                <th>Nama Siswa</th>
                <th>Jenis Makan</th>
                <th>Waktu Input</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loadingLogs">
                <td colspan="5" class="text-center py-3">Memuat riwayat hari ini...</td>
              </tr>
              <tr v-else-if="!todayLogs.logs || todayLogs.logs.length === 0">
                <td colspan="5" class="empty-state">Belum ada presensi makan yang diinput hari ini.</td>
              </tr>
              <tr v-else v-for="log in todayLogs.logs" :key="log.id">
                <td>{{ log.no }}</td>
                <td><span class="num-tag">#{{ log.dining_number }}</span></td>
                <td class="font-bold">{{ log.student_name }}</td>
                <td>
                  <span :class="['badge-meal', log.jenis_makan.toLowerCase()]">
                    {{ log.jenis_makan }}
                  </span>
                </td>
                <td class="time-col">{{ log.waktu }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const diningInputRef = ref(null);

const diningNumber = ref('');
const mealTime = ref(autoDetectMealTime());
const submitting = ref(false);
const loadingLogs = ref(false);
const feedback = ref(null);

const todayLogs = ref({
  today: '',
  total_input_today: 0,
  logs: [],
});

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

function autoDetectMealTime() {
  const hour = new Date().getHours();
  if (hour < 10) return 'pagi';
  if (hour < 15) return 'siang';
  return 'sore';
}

async function submitMealInput() {
  if (!diningNumber.value) return;
  submitting.value = true;
  feedback.value = null;

  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/staff/dining/input', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        dining_number: diningNumber.value,
        meal_time: mealTime.value,
      }),
    });

    const data = await res.json();

    if (res.ok) {
      feedback.value = {
        type: 'success',
        message: data.message || 'Presensi makan berhasil dicatat!',
      };
      diningNumber.value = '';
      fetchTodayLogs();
      if (diningInputRef.value) diningInputRef.value.focus();
    } else {
      feedback.value = {
        type: 'error',
        message: data.message || 'Gagal menyimpan presensi makan.',
      };
    }
  } catch (e) {
    feedback.value = {
      type: 'error',
      message: 'Terjadi kesalahan koneksi.',
    };
  } finally {
    submitting.value = false;
  }
}

async function fetchTodayLogs() {
  loadingLogs.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch('/api/staff/dining/logs', {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/json',
      },
    });
    if (res.ok) {
      todayLogs.value = await res.json();
    }
  } catch (e) {
    console.error('Failed to fetch today logs:', e);
  } finally {
    loadingLogs.value = false;
  }
}

async function handleLogout() {
  await authStore.logout();
  router.push('/staff');
}

onMounted(() => {
  fetchTodayLogs();
  if (diningInputRef.value) diningInputRef.value.focus();
});
</script>

<style scoped>
.staff-dashboard {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  font-family: inherit;
}

.staff-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 2.5rem;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo {
  font-size: 1.5rem;
}

.staff-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #0f1e3c;
  margin: 0;
}

.staff-user {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.staff-name {
  font-weight: 600;
  color: #334155;
  font-size: 0.95rem;
}

.btn-logout {
  padding: 0.5rem 1rem;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-logout:hover {
  background: #fef2f2;
  color: #ef4444;
  border-color: #fca5a5;
}

.staff-content {
  flex: 1;
  padding: 2rem 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

.dining-input-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
}

.card-header h2 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f1e3c;
  margin: 0 0 0.4rem;
}

.card-header p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0 0 1.5rem;
}

.input-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.input-wrapper {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 2px solid #cbd5e1;
  border-radius: 12px;
  padding: 0.2rem 1rem;
  transition: border-color 0.2s;
}

.input-wrapper:focus-within {
  border-color: #3b82f6;
  background: #ffffff;
}

.prefix {
  font-size: 1.5rem;
  font-weight: 800;
  color: #94a3b8;
  margin-right: 0.5rem;
}

.input-wrapper input {
  width: 100%;
  border: none;
  background: transparent;
  padding: 0.75rem 0;
  font-size: 1.25rem;
  font-weight: 700;
  outline: none;
  color: #0f1e3c;
}

.meal-options {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.meal-radio {
  flex: 1;
  min-width: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.85rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  color: #475569;
  background: #f8fafc;
  transition: all 0.2s;
}

.meal-radio input {
  display: none;
}

.meal-radio.active {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1d4ed8;
}

.submit-btn {
  background: #10b981;
  color: #ffffff;
  padding: 1rem;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #059669;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.feedback-alert {
  margin-top: 1.25rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.feedback-alert.success {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.feedback-alert.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Today Logs Table */
.today-logs-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-center;
  margin-bottom: 1.25rem;
}

.table-header h3 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f1e3c;
  margin: 0 0 0.25rem;
}

.table-header p {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
}

.refresh-btn {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 0.5rem 0.9rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
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
  padding: 0.85rem 1rem;
  border-bottom: 2px solid #e2e8f0;
}

.data-table td {
  padding: 0.85rem 1rem;
  font-size: 0.9rem;
  border-bottom: 1px solid #f1f5f9;
}

.num-tag {
  font-family: monospace;
  font-weight: 800;
  color: #1d4ed8;
  background: #eff6ff;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}

.badge-meal {
  font-weight: 700;
  font-size: 0.8rem;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
}

.badge-meal.pagi { background: #fffbebfb; color: #b45309; }
.badge-meal.siang { background: #ecfdf5; color: #047857; }
.badge-meal.sore { background: #f0f9ff; color: #0369a1; }

.empty-state {
  text-align: center;
  color: #94a3b8;
  padding: 2rem !important;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
