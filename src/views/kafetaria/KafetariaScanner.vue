<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Input Presensi Makan</h1>
      <p class="page-subtitle">Masukkan nomor makan (identitas dining) siswa untuk mencatat kehadiran</p>
    </div>

    <div class="scanner-layout">
      <!-- Scanner Section (Now Manual Input Only) -->
      <div class="scanner-section">
        <div class="card scanner-card">
          <div class="card-header">
            <h3>Input Nomor Makan</h3>
            <select v-model="selectedMealTime" class="meal-selector">
              <option value="Pagi">Makan Pagi (Sarapan)</option>
              <option value="Siang">Makan Siang</option>
              <option value="Sore">Makan Sore</option>
            </select>
          </div>
          
          <div class="manual-form-container">
            <p class="instruction-text">Ketik nomor makan yang terdaftar sesuai dengan nomor pendaftaran murid.</p>
            <form @submit.prevent="handleManualInput" class="manual-form">
              <input type="text" v-model="manualNumber" placeholder="Contoh: 7 atau 007" required class="large-input">
              <button type="submit" class="btn btn-primary large-btn" :disabled="loading">
                <span v-if="loading" class="spinner-small"></span>
                <span v-else>Catat Presensi</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Result Section -->
      <div class="result-section">
        <div class="card result-card">
          <h3>Status Pemindaian</h3>
          
          <div v-if="lastResult" class="result-box" :class="lastResult.status">
            <div class="result-icon">
              {{ lastResult.status === 'success' ? '✅' : '❌' }}
            </div>
            <h2 class="result-title">{{ lastResult.title }}</h2>
            <p class="result-message">{{ lastResult.message }}</p>
            
            <div class="student-info" v-if="lastResult.student">
              <div class="info-row">
                <span>Nama Siswa:</span>
                <strong>{{ lastResult.student.name }}</strong>
              </div>
              <div class="info-row" v-if="lastResult.student.dining_number">
                <span>Dining Number:</span>
                <strong>{{ lastResult.student.dining_number }}</strong>
              </div>
              <div class="info-row">
                <span>Sesi Makan:</span>
                <span class="badge">{{ lastResult.meal_time }}</span>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-result">
            <div class="empty-icon">📝</div>
            <p>Belum ada data yang diinput.</p>
            <p class="text-sm">Silakan masukkan nomor makan pada form di samping.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { request } from '@/api/auth';

const selectedMealTime = ref('Pagi');
const loading = ref(false);
const manualNumber = ref('');
const lastResult = ref(null);

// Tentukan default waktu makan berdasarkan jam
const currentHour = new Date().getHours();
if (currentHour >= 5 && currentHour < 11) {
  selectedMealTime.value = 'Pagi';
} else if (currentHour >= 11 && currentHour < 15) {
  selectedMealTime.value = 'Siang';
} else {
  selectedMealTime.value = 'Sore';
}

async function handleManualInput() {
  if (!manualNumber.value) return;
  await processAttendance(manualNumber.value);
  manualNumber.value = '';
}

async function processAttendance(diningNumber) {
  loading.value = true;
  try {
    const response = await request('/kafetaria/scan', {
      method: 'POST',
      body: JSON.stringify({
        dining_number: diningNumber,
        meal_time: selectedMealTime.value
      })
    });
    
    lastResult.value = {
      status: 'success',
      title: 'Berhasil',
      message: response.message,
      student: response.student,
      meal_time: response.meal_time
    };
    
    // Suara sukses
    playSound('success');
    
  } catch (error) {
    lastResult.value = {
      status: 'error',
      title: 'Ditolak',
      message: error.message || 'Terjadi kesalahan sistem',
      student: error.student, // if api.js adds student to error object
      meal_time: selectedMealTime.value
    };
    
    // Suara gagal
    playSound('error');
  } finally {
    loading.value = false;
  }
}

function playSound(type) {
  // Opsi fitur tambahan untuk membunyikan alert
  try {
    const freq = type === 'success' ? 800 : 300;
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();
    
    oscillator.type = type === 'success' ? 'sine' : 'sawtooth';
    oscillator.frequency.setValueAtTime(freq, audioCtx.currentTime);
    
    gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
    
    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);
    
    oscillator.start();
    oscillator.stop(audioCtx.currentTime + 0.5);
  } catch (e) {
    console.error("Audio not supported");
  }
}

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

.scanner-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.card h3 {
  margin: 0 0 1rem 0;
  color: #1e293b;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-header h3 {
  margin: 0;
}

.meal-selector {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  font-weight: 500;
  color: #334155;
  outline: none;
}

.manual-form-container {
  padding: 1rem 0;
}

.instruction-text {
  color: #64748b;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

.manual-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.large-input {
  width: 100%;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  font-size: 1.25rem;
  font-weight: 500;
  color: #1e293b;
  transition: all 0.2s;
  background: #f8fafc;
}

.large-input:focus {
  outline: none;
  border-color: #f59e0b;
  background: white;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
}

.large-btn {
  padding: 1rem;
  font-size: 1.1rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner-small {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.scanner-controls {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #f59e0b;
  color: white;
}
.btn-primary:hover:not(:disabled) { background: #d97706; }
.btn:disabled { opacity: 0.7; cursor: not-allowed; }

.result-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.empty-result {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #64748b;
  text-align: center;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.text-sm { font-size: 0.875rem; }

.result-box {
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  margin-top: 1rem;
}

.result-box.success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.result-box.error {
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.result-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.result-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  color: #1e293b;
}

.result-message {
  color: #475569;
  margin-bottom: 1.5rem;
}

.student-info {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: left;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}
.info-row:last-child { border-bottom: none; }
.info-row span { color: #64748b; }
.info-row strong { color: #1e293b; }

.badge {
  background: #e0f2fe;
  color: #0284c7;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.875rem;
}

@media (max-width: 1024px) {
  .scanner-layout {
    grid-template-columns: 1fr;
  }
}
</style>
