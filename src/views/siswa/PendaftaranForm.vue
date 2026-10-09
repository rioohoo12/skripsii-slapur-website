<template>
  <div class="pendaftaran-chatbot-page" :class="themeClass">
    <!-- Header Section -->
    <div class="header-section">
      <div class="title-badge">
        <span class="icon font-bold">🤖</span>
        <span>Pendaftaran Otomatis via Custom Chatbot</span>
      </div>
      <h2 class="page-title">Asisten Pendaftaran Siswa Baru</h2>
      <p class="page-subtitle">
        Proses pendaftaran dilakukan sepenuhnya melalui percakapan dengan Custom Chatbot di bawah ini. Anda tidak perlu menginput formulir secara manual.
      </p>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="alert success-alert">
      <span class="icon">✅</span>
      <div class="content">
        <h4>Pendaftaran Berhasil!</h4>
        <p>{{ successMsg }}</p>
      </div>
    </div>

    <div v-if="errorMsg" class="alert error-alert">
      <span class="icon">⚠️</span>
      <div class="content">
        <h4>Terjadi Kesalahan</h4>
        <p>{{ errorMsg }}</p>
      </div>
    </div>

    <!-- Main Grid: Chatbot Window + Live Data Preview Card -->
    <div class="chatbot-grid">
      <!-- Chatbot Interactive Container -->
      <div class="chat-container-card">
        <!-- Chat Header -->
        <div class="chat-header">
          <div class="bot-info">
            <div class="bot-avatar">🤖</div>
            <div>
              <h3>Otak VA Pendaftaran SLAPUR</h3>
              <span class="online-status">● Berinteraksi Langsung</span>
            </div>
          </div>
          <button @click="resetConversation" class="reset-btn" title="Reset Percakapan">
            🔄 Mulai Ulang
          </button>
        </div>

        <!-- Chat Messages Area -->
        <div class="chat-messages" ref="messagesContainer">
          <div 
            v-for="(msg, idx) in messages" 
            :key="idx"
            :class="['message-bubble', msg.role === 'user' ? 'user-msg' : 'bot-msg']"
          >
            <div class="msg-content whitespace-pre-line">
              {{ msg.text }}
            </div>
          </div>

          <div v-if="loadingBot" class="typing-indicator">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="text">Bot sedang menyusun pertanyaan...</span>
          </div>
        </div>

        <!-- Quick Answer Options (If Awaiting Specific Selection) -->
        <div v-if="currentOptions.length > 0 && !loadingBot" class="quick-options">
          <button 
            v-for="(opt, oIdx) in currentOptions" 
            :key="oIdx"
            @click="selectOption(opt)"
            class="opt-btn"
          >
            {{ opt.label || opt }}
          </button>
        </div>

        <!-- Chat Input Form -->
        <form @submit.prevent="sendMessage" class="chat-input-bar">
          <input 
            v-model="userMsg" 
            type="text" 
            placeholder="Jawab pertanyaan bot di sini (misal: Nama Lengkap, Laki-laki, dll)..." 
            :disabled="loadingBot || isSubmitting"
            ref="inputRef"
          />
          <button type="submit" :disabled="!userMsg.trim() || loadingBot || isSubmitting" class="send-btn">
            Kirim
          </button>
        </form>
      </div>

      <!-- Live Summary Preview Panel -->
      <div class="preview-panel-card">
        <div class="preview-header">
          <h3>Ringkasan Data Terkumpul</h3>
          <span class="progress-percent">{{ progressPercentage }}% Terisi</span>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>

        <!-- Categorized Data Lists -->
        <div class="summary-groups">
          <!-- Data Pribadi -->
          <div class="group-box">
            <h4 class="group-title">👤 Data Pribadi</h4>
            <div class="item-row">
              <span class="label">Nama Lengkap:</span>
              <span class="val" :class="{ filled: form.full_name }">{{ form.full_name || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Jenis Kelamin:</span>
              <span class="val capitalize" :class="{ filled: form.gender }">{{ form.gender || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Tempat Lahir:</span>
              <span class="val" :class="{ filled: form.tempat_lahir }">{{ form.tempat_lahir || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Tanggal Lahir:</span>
              <span class="val" :class="{ filled: form.tanggal_lahir }">{{ form.tanggal_lahir || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Agama:</span>
              <span class="val" :class="{ filled: form.agama }">{{ form.agama || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Alamat:</span>
              <span class="val" :class="{ filled: form.alamat }">{{ form.alamat || 'Belum diisi' }}</span>
            </div>
          </div>

          <!-- Data Pendidikan -->
          <div class="group-box">
            <h4 class="group-title">🎓 Data Pendidikan</h4>
            <div class="item-row">
              <span class="label">Kelas Pendaftaran:</span>
              <span class="val" :class="{ filled: form.kelas_yang_didaftar }">
                {{ form.kelas_yang_didaftar ? `Kelas ${form.kelas_yang_didaftar}` : 'Belum diisi' }}
              </span>
            </div>
            <div class="item-row">
              <span class="label">Asal Sekolah:</span>
              <span class="val" :class="{ filled: form.previous_school_name }">{{ form.previous_school_name || 'Belum diisi' }}</span>
            </div>
          </div>

          <!-- Data Orang Tua / Wali -->
          <div class="group-box">
            <h4 class="group-title">👨‍👩‍👦 Data Orang Tua / Wali</h4>
            <div class="item-row">
              <span class="label">Nama Ayah:</span>
              <span class="val" :class="{ filled: form.nama_ayah }">{{ form.nama_ayah || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Pekerjaan Ayah:</span>
              <span class="val" :class="{ filled: form.pekerjaan_ayah }">{{ form.pekerjaan_ayah || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Nama Ibu:</span>
              <span class="val" :class="{ filled: form.nama_ibu }">{{ form.nama_ibu || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">Pekerjaan Ibu:</span>
              <span class="val" :class="{ filled: form.pekerjaan_ibu }">{{ form.pekerjaan_ibu || 'Belum diisi' }}</span>
            </div>
            <div class="item-row">
              <span class="label">No. HP Ortus/Wali:</span>
              <span class="val" :class="{ filled: form.no_telp_ortu }">{{ form.no_telp_ortu || 'Belum diisi' }}</span>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="submit-wrap">
          <button 
            @click="handleSubmit" 
            class="submit-btn" 
            :disabled="isSubmitting || !isFormComplete"
          >
            <span v-if="isSubmitting" class="spinner"></span>
            {{ isSubmitting ? 'Menyimpan Pendaftaran...' : 'Simpan & Kirim Pendaftaran' }}
          </button>
          <small v-if="!isFormComplete" class="hint-text">
            *Lengkapi seluruh data melalui chatbot di samping untuk mengaktifkan tombol simpan.
          </small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { pendaftaranApi } from '@/api/pendaftaran';
import { chatbotApi } from '@/api/chatbot';

const props = defineProps({
  jenisKelamin: { type: String, default: 'laki-laki' },
});

const themeClass = computed(() => 'theme-' + props.jenisKelamin);
const router = useRouter();
const authStore = useAuthStore();

const isSubmitting = ref(false);
const loadingBot = ref(false);
const errorMsg = ref('');
const successMsg = ref('');
const userMsg = ref('');
const messagesContainer = ref(null);

const messages = ref([]);
const currentOptions = ref([]);

// Form State Object
const form = reactive({
  full_name: '',
  gender: props.jenisKelamin,
  tempat_lahir: '',
  tanggal_lahir: '',
  alamat: '',
  is_transfer_student: false,
  previous_school_name: '',
  kelas_yang_didaftar: '',
  agama: 'Islam',
  golongan_darah: 'O',
  kewarganegaraan: 'WNI',
  no_telp: '',
  nama_ayah: '',
  pendidikan_ayah: 'SMA',
  pekerjaan_ayah: '',
  penghasilan_ayah: '3.000.000',
  no_telp_ayah: '',
  agama_ayah: 'Islam',
  kewarganegaraan_ayah: 'WNI',
  nama_ibu: '',
  pendidikan_ibu: 'SMA',
  pekerjaan_ibu: '',
  penghasilan_ibu: '2.000.000',
  no_telp_ibu: '',
  agama_ibu: 'Islam',
  kewarganegaraan_ibu: 'WNI',
  no_telp_ortu: '',
});

// Calculate completion percentage
const isFormComplete = computed(() => {
  return !!(
    form.full_name &&
    form.gender &&
    form.tempat_lahir &&
    form.tanggal_lahir &&
    form.agama &&
    form.alamat &&
    form.kelas_yang_didaftar &&
    form.previous_school_name &&
    form.nama_ayah &&
    form.pekerjaan_ayah &&
    form.nama_ibu &&
    form.pekerjaan_ibu &&
    form.no_telp_ortu
  );
});

const progressPercentage = computed(() => {
  const fields = [
    form.full_name,
    form.gender,
    form.tempat_lahir,
    form.tanggal_lahir,
    form.agama,
    form.alamat,
    form.kelas_yang_didaftar,
    form.previous_school_name,
    form.nama_ayah,
    form.pekerjaan_ayah,
    form.nama_ibu,
    form.pekerjaan_ibu,
    form.no_telp_ortu
  ];
  const filledCount = fields.filter((val) => typeof val === 'string' && val.trim() !== '').length;
  return Math.round((filledCount / fields.length) * 100);
});

async function scrollToBottom() {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function initBotGreeting() {
  const defaultName = authStore.user?.name || 'Calon Siswa';
  form.full_name = authStore.user?.name || '';
  form.gender = authStore.user?.jenis_kelamin || props.jenisKelamin;

  messages.value = [
    {
      role: 'assistant',
      text: `Halo ${defaultName}! 👋 Selamat datang di Pendaftaran Siswa Baru SLAPUR.\n\nSaya asisten virtual khusus pendaftaran. Seluruh data Anda akan dikumpulkan melalui percakapan ini secara otomatis.\n\nMari kita mulai! Siapa nama lengkap Anda (sesuai akte kelahiran)?`,
    },
  ];

  // Fast trigger helper options for gender or class if needed
  currentOptions.value = [];
}

async function sendMessage() {
  if (!userMsg.value.trim() || loadingBot.value) return;

  const text = userMsg.value.trim();
  userMsg.value = '';

  messages.value.push({
    role: 'user',
    text: text,
  });

  await scrollToBottom();
  loadingBot.value = true;
  currentOptions.value = [];

  try {
    const sessionId = localStorage.getItem('chat_session_id');
    const res = await chatbotApi.sendMessage(sessionId, text);

    if (res.session_id) {
      localStorage.setItem('chat_session_id', res.session_id);
    }

    // Extract slots returned by backend custom chatbot
    if (res.slots) {
      updateFormFromSlots(res.slots);
    }

    const replyMsg = res.reply?.message || res.reply || 'Terima kasih, data sudah dicatat.';
    messages.value.push({
      role: 'assistant',
      text: replyMsg,
    });

    // Provide quick dynamic buttons if step asks specific choices
    if (replyMsg.toLowerCase().includes('jenis kelamin')) {
      currentOptions.value = ['Laki-laki', 'Perempuan'];
    } else if (replyMsg.toLowerCase().includes('kelas berapa') || replyMsg.toLowerCase().includes('jenjang')) {
      currentOptions.value = ['Kelas 7 (SMP)', 'Kelas 8 (SMP)', 'Kelas 9 (SMP)', 'Kelas 10 (SMA)', 'Kelas 11 (SMA)', 'Kelas 12 (SMA)'];
    }

  } catch (err) {
    console.error('Chat error:', err);
    messages.value.push({
      role: 'assistant',
      text: 'Maaf, terjadi kendala koneksi. Namun data Anda tetap dicatat.',
    });
  } finally {
    loadingBot.value = false;
    await scrollToBottom();
  }
}

function selectOption(opt) {
  userMsg.value = typeof opt === 'object' ? opt.label : opt;
  sendMessage();
}

function updateFormFromSlots(slots) {
  const isValid = (val) => typeof val === 'string' && val.trim() !== '' && val !== '-' && val !== 'belum diisi';

  if (isValid(slots.nama)) form.full_name = slots.nama;
  if (isValid(slots.jenis_kelamin)) form.gender = slots.jenis_kelamin.toLowerCase();
  if (isValid(slots.tempat_lahir)) form.tempat_lahir = slots.tempat_lahir;
  if (isValid(slots.tanggal_lahir)) form.tanggal_lahir = slots.tanggal_lahir;
  if (isValid(slots.agama)) form.agama = slots.agama;
  if (isValid(slots.alamat)) form.alamat = slots.alamat;
  if (isValid(slots.kelas)) form.kelas_yang_didaftar = String(slots.kelas);
  if (isValid(slots.asal_sekolah)) form.previous_school_name = slots.asal_sekolah;

  // Data Orang Tua / Wali
  if (isValid(slots.nama_ayah)) form.nama_ayah = slots.nama_ayah;
  if (isValid(slots.pekerjaan_ayah)) form.pekerjaan_ayah = slots.pekerjaan_ayah;
  if (isValid(slots.nama_ibu)) form.nama_ibu = slots.nama_ibu;
  if (isValid(slots.pekerjaan_ibu)) form.pekerjaan_ibu = slots.pekerjaan_ibu;
  if (isValid(slots.telepon)) form.no_telp_ortu = slots.telepon;
}

function resetConversation() {
  initBotGreeting();
}

async function handleSubmit() {
  errorMsg.value = '';
  successMsg.value = '';

  if (!isFormComplete.value) {
    errorMsg.value = 'Data pendaftaran belum lengkap. Silakan selesaikan sesi tanya jawab chatbot.';
    return;
  }

  isSubmitting.value = true;
  try {
    const res = await pendaftaranApi.submitForm(form);
    successMsg.value = res.message || 'Pendaftaran Anda berhasil dikirim dan dicatat!';
    
    setTimeout(() => {
      router.push(`/siswa/${props.jenisKelamin}/pendaftaran/status`);
    }, 1500);

  } catch (e) {
    errorMsg.value = e.message || 'Gagal menyimpan pendaftaran. Periksa koneksi Anda.';
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  initBotGreeting();
});
</script>

<style scoped>
.pendaftaran-chatbot-page {
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 3rem;
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}

.header-section {
  margin-bottom: 1.5rem;
}

.title-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #ccfbf1;
  color: #0f766e;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.page-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.35rem 0;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0;
}

.alert {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  margin-bottom: 1.25rem;
}

.success-alert {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

.error-alert {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

/* Grid Layout */
.chatbot-grid {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

/* Chat Container Card */
.chat-container-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  height: 650px;
  overflow: hidden;
}

.chat-header {
  background: linear-gradient(135deg, #0f766e, #115e59);
  color: #ffffff;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.bot-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.bot-avatar {
  width: 38px;
  height: 38px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.bot-info h3 {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
}

.online-status {
  font-size: 0.75rem;
  color: #5eead4;
}

.reset-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #ffffff;
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.reset-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

.chat-messages {
  flex: 1;
  padding: 1.25rem;
  overflow-y: auto;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message-bubble {
  max-width: 82%;
  padding: 0.85rem 1.1rem;
  border-radius: 16px;
  font-size: 0.9rem;
  line-height: 1.5;
  word-break: break-word;
}

.bot-msg {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  align-self: flex-start;
  border-top-left-radius: 2px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.user-msg {
  background: #0f766e;
  color: #ffffff;
  align-self: flex-end;
  border-top-right-radius: 2px;
}

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: #64748b;
  padding: 0.5rem 0.75rem;
}

.typing-indicator .dot {
  width: 6px;
  height: 6px;
  background: #0f766e;
  border-radius: 50%;
  animation: bounce 1.2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

.quick-options {
  padding: 0.65rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
}

.opt-btn {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.opt-btn:hover {
  background: #dcfce7;
}

.chat-input-bar {
  padding: 0.85rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 0.65rem;
}

.chat-input-bar input {
  flex: 1;
  padding: 0.65rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-size: 0.85rem;
  outline: none;
  transition: border-color 0.2s;
}

.chat-input-bar input:focus {
  border-color: #0f766e;
}

.send-btn {
  background: #0f766e;
  color: #ffffff;
  border: none;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.send-btn:hover:not(:disabled) {
  background: #115e59;
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Preview Panel */
.preview-panel-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 1.25rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.preview-header h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.progress-percent {
  font-size: 0.8rem;
  font-weight: 700;
  color: #0f766e;
  background: #f0fdf4;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
}

.progress-bar-bg {
  height: 8px;
  background: #f1f5f9;
  border-radius: 999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #0f766e, #10b981);
  transition: width 0.3s ease;
}

.summary-groups {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 0.25rem;
}

.group-box {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  padding: 0.85rem;
}

.group-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: #334155;
  margin: 0 0 0.5rem 0;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.35rem;
}

.item-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  margin-bottom: 0.35rem;
}

.item-row .label {
  color: #64748b;
}

.item-row .val {
  color: #94a3b8;
  font-weight: 600;
}

.item-row .val.filled {
  color: #0f766e;
}

.submit-wrap {
  margin-top: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.submit-btn {
  width: 100%;
  background: #0f766e;
  color: white;
  border: none;
  padding: 0.85rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #115e59;
}

.submit-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.hint-text {
  font-size: 0.75rem;
  color: #94a3b8;
  text-align: center;
}

@media (max-width: 900px) {
  .chatbot-grid {
    grid-template-columns: 1fr;
  }
}
</style>
