<template>
  <div class="permohonan-page" :class="themeClass">
    <h2 class="page-heading">Permohonan Pendaftaran</h2>
    <p class="page-subtitle">Daftar dan verifikasi pendaftaran dibantu asisten chatbot SLAPUR.</p>

    <div class="chatbot-layout">
      <PageCard :jenis-kelamin="jenisKelamin" class="card-chatbot">
        <template #header>
          <span class="chatbot-header">
            <span class="chatbot-avatar">🤖</span>
            Asisten Pendaftaran SLAPUR
          </span>
        </template>
        <div class="chatbot-body">
          <div ref="messagesEnd" class="messages-wrap">
            <template v-if="messages.length === 0 && !loadingHistory">
              <div class="chat-bubble bot welcome">
                <p><strong>Anda bisa:</strong></p>
                <ol class="welcome-list">
                  <li>Cara Daftar</li>
                  <li>Syarat Pendaftaran</li>
                  <li>Verifikasi Pendaftaran</li>
                  <li>Tanya Biaya Pendaftaran dan SPP</li>
                  <li>Ketik Bantuan</li>
                </ol>
              </div>
            </template>
            <template v-else>
              <div
                v-for="(m, i) in messages"
                :key="i"
                class="chat-bubble"
                :class="[m.role, { bot: m.role === 'assistant' }]"
              >
                <div class="bubble-content" v-html="formatMessage(m.message)"></div>
                <span class="bubble-time">{{ formatTime(m.created_at) }}</span>
              </div>
            </template>
            <div v-if="loading" class="chat-bubble bot typing">
              <span class="typing-dots">...</span>
            </div>
          </div>

          <div class="chat-actions">
            <button
              type="button"
              class="btn-hapus-chat"
              :disabled="loading || clearingChat"
              @click="hapusChat"
              title="Hapus percakapan dan mulai baru"
            >
              {{ clearingChat ? 'Menghapus...' : 'Hapus Chat' }}
            </button>
          </div>
          <form class="chat-form" @submit.prevent="sendMessage">
            <input
              ref="fileInputRef"
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
              class="chat-file-input"
              @change="onBuktiFileSelect"
            />
            <input
              v-model="inputText"
              type="text"
              class="chat-input"
              placeholder="Ketik nomor (1–5) atau pesan (cara daftar, syarat, verifikasi, biaya, bantuan)..."
              :disabled="loading || uploadingBukti"
              autocomplete="off"
            />
            <button type="button" class="chat-upload-bukti" title="Upload bukti pembayaran (foto transfer)" :disabled="loading || uploadingBukti" @click="triggerBuktiUpload">
              📎 Bukti
            </button>
            <button type="submit" class="chat-send" :disabled="loading || uploadingBukti || !inputText.trim()" title="Kirim">
              Kirim
            </button>
          </form>
          <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        </div>
      </PageCard>

      <aside class="chatbot-sidebar">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-info">
          <template #header>Panduan Cepat</template>
          <p class="info-desc">Ketik nomor atau kata kunci di chat.</p>
          <ul class="info-list">
            <li><strong>1</strong> / <strong>cara daftar</strong> — Cara Daftar</li>
            <li><strong>2</strong> / <strong>syarat</strong> — Syarat Pendaftaran</li>
            <li><strong>3</strong> / <strong>verifikasi</strong> — Verifikasi Pendaftaran</li>
            <li><strong>4</strong> / <strong>biaya</strong> — Biaya Pendaftaran & SPP</li>
            <li><strong>5</strong> / <strong>bantuan</strong> — Bantuan</li>
          </ul>
          <p class="info-hapus">Gunakan <strong>Hapus Chat</strong> untuk mengosongkan percakapan dan melihat lagi opsi di atas.</p>
        </PageCard>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import PageCard from '@/components/PageCard.vue';
import { chatbotApi } from '@/api/chatbot.js';
import { pendaftaranApi } from '@/api/pendaftaran.js';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const STORAGE_KEY = 'slapur_chatbot_session_id';

const messages = ref([]);
const inputText = ref('');
const loading = ref(false);
const loadingHistory = ref(false);
const clearingChat = ref(false);
const uploadingBukti = ref(false);
const errorMsg = ref('');
const sessionId = ref(localStorage.getItem(STORAGE_KEY) || '');
const messagesEnd = ref(null);
const fileInputRef = ref(null);

function formatMessage(text) {
  if (!text) return '';
  return text
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br/>');
}

function formatTime(iso) {
  if (!iso) return '';
  try {
    const d = new Date(iso);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}

async function loadHistory() {
  if (!sessionId.value) return;
  loadingHistory.value = true;
  errorMsg.value = '';
  try {
    const data = await chatbotApi.getHistory(sessionId.value);
    messages.value = (data.messages || []).map((m) => ({
      role: m.role,
      message: m.message,
      created_at: m.created_at,
    }));
  } catch (e) {
    messages.value = [];
  }
  loadingHistory.value = false;
}

async function sendMessage() {
  const text = inputText.value.trim();
  if (!text || loading.value) return;
  inputText.value = '';
  errorMsg.value = '';

  messages.value.push({ role: 'user', message: text, created_at: new Date().toISOString() });
  loading.value = true;
  scrollToEnd();

  try {
    const data = await chatbotApi.sendMessage(sessionId.value || undefined, text);
    if (data.session_id) {
      sessionId.value = data.session_id;
      localStorage.setItem(STORAGE_KEY, data.session_id);
    }
    messages.value.push({
      role: 'assistant',
      message: data.reply || 'Maaf, tidak ada balasan.',
      created_at: new Date().toISOString(),
    });
  } catch (e) {
    errorMsg.value = e.message || 'Gagal mengirim. Pastikan backend berjalan (php artisan serve).';
    messages.value.push({
      role: 'assistant',
      message: 'Pesan tidak terkirim. Silakan cek koneksi dan coba lagi.',
      created_at: new Date().toISOString(),
    });
  }
  loading.value = false;
  await nextTick();
  scrollToEnd();
}

function scrollToEnd() {
  nextTick(() => {
    const el = messagesEnd.value;
    if (el) el.scrollTop = el.scrollHeight;
  });
}

function triggerBuktiUpload() {
  if (fileInputRef.value) fileInputRef.value.click();
}

async function onBuktiFileSelect(ev) {
  const file = ev.target.files?.[0];
  if (!file || loading.value || uploadingBukti.value) return;
  ev.target.value = '';
  const maxSize = 5 * 1024 * 1024;
  if (file.size > maxSize) {
    errorMsg.value = 'Ukuran file maksimal 5 MB.';
    return;
  }
  uploadingBukti.value = true;
  errorMsg.value = '';
  try {
    await pendaftaranApi.uploadBuktiPembayaran(file);
    const text = 'Saya sudah upload bukti pembayaran';
    messages.value.push({ role: 'user', message: text, created_at: new Date().toISOString() });
    loading.value = true;
    scrollToEnd();
    const data = await chatbotApi.sendMessage(sessionId.value || undefined, text);
    if (data.session_id) {
      sessionId.value = data.session_id;
      localStorage.setItem(STORAGE_KEY, data.session_id);
    }
    messages.value.push({
      role: 'assistant',
      message: data.reply || 'Bukti pembayaran diterima. Menunggu verifikasi admin.',
      created_at: new Date().toISOString(),
    });
  } catch (e) {
    errorMsg.value = e.message || 'Gagal upload bukti. Pastikan file berupa gambar (max 5 MB).';
  } finally {
    loading.value = false;
    uploadingBukti.value = false;
    await nextTick();
    scrollToEnd();
  }
}

async function hapusChat() {
  if (loading.value || clearingChat.value) return;
  clearingChat.value = true;
  errorMsg.value = '';
  try {
    if (sessionId.value) {
      await chatbotApi.clearChat(sessionId.value);
    }
    messages.value = [];
    sessionId.value = '';
    localStorage.removeItem(STORAGE_KEY);
  } catch (e) {
    messages.value = [];
    sessionId.value = '';
    localStorage.removeItem(STORAGE_KEY);
    errorMsg.value = e.message || 'Chat dihapus lokal; server mungkin offline.';
  }
  clearingChat.value = false;
}

onMounted(() => {
  if (sessionId.value) loadHistory();
  else scrollToEnd();
});

watch(messages, () => nextTick(() => scrollToEnd()), { deep: true });
</script>

<style scoped>
.permohonan-page {
  padding-bottom: 2rem;
}

.page-heading {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0 0 1.5rem 0;
}

.chatbot-layout {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .chatbot-layout {
    grid-template-columns: 1fr;
  }
}

.card-chatbot :deep(.page-card-body) {
  padding: 0;
  display: flex;
  flex-direction: column;
  max-height: 70vh;
}

.chatbot-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.chatbot-avatar {
  font-size: 1.5rem;
}

.chatbot-body {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 320px;
}

.messages-wrap {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.mt-1 { margin-top: 0.5rem; }

.chat-bubble {
  max-width: 88%;
  padding: 0.85rem 1.1rem;
  border-radius: 16px;
  font-size: 0.95rem;
  line-height: 1.5;
}

.chat-bubble.bot {
  align-self: flex-start;
  background: #f1f5f9;
  color: #334155;
  border-bottom-left-radius: 4px;
}

.chat-bubble.bot.welcome {
  background: #e2e8f0;
  border: 1px solid #e2e8f0;
}

.welcome-list {
  margin: 0.5rem 0 0 1rem;
  padding-left: 0.5rem;
  line-height: 1.8;
}

.chat-actions {
  padding: 0.5rem 1.25rem 0;
  border-top: 1px solid #e2e8f0;
}

.btn-hapus-chat {
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
  cursor: pointer;
}

.btn-hapus-chat:hover:not(:disabled) {
  background: #f1f5f9;
  color: #475569;
}

.btn-hapus-chat:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.permohonan-page.theme-laki-laki .chat-bubble.user {
  align-self: flex-end;
  background: #ccfbf1;
  color: #134e4a;
  border-bottom-right-radius: 4px;
}

.permohonan-page.theme-perempuan .chat-bubble.user {
  align-self: flex-end;
  background: #ede9fe;
  color: #4c1d95;
  border-bottom-right-radius: 4px;
}

.bubble-content :deep(strong) {
  font-weight: 700;
}

.bubble-time {
  display: block;
  font-size: 0.75rem;
  opacity: 0.8;
  margin-top: 0.35rem;
}

.chat-bubble.typing .typing-dots {
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

.chat-form {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.25rem;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
}

.chat-file-input {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.chat-input {
  flex: 1;
  min-width: 120px;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
}

.chat-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
}

.permohonan-page.theme-perempuan .chat-input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.1);
}

.chat-upload-bukti {
  padding: 0.75rem 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #f8fafc;
  color: #475569;
  font-size: 0.9rem;
  cursor: pointer;
  white-space: nowrap;
}

.chat-upload-bukti:hover:not(:disabled) {
  background: #f1f5f9;
  color: #334155;
}

.chat-upload-bukti:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.chat-send {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 12px;
  background: var(--primary);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.95rem;
}

.chat-send:hover:not(:disabled) {
  opacity: 0.95;
}

.chat-send:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.permohonan-page.theme-laki-laki .chat-send {
  background: #0f766e;
}

.permohonan-page.theme-perempuan .chat-send {
  background: #7c3aed;
}

.error-msg {
  font-size: 0.875rem;
  color: #dc2626;
  padding: 0 1.25rem 1rem;
}

.chatbot-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.card-info :deep(.page-card-body) {
  padding: 1.25rem;
}

.info-desc {
  margin: 0 0 0.75rem 0;
  font-size: 0.9rem;
  color: #64748b;
}

.info-list {
  margin: 0;
  padding-left: 1.25rem;
  color: #475569;
  font-size: 0.9rem;
  line-height: 1.9;
}

.info-list strong {
  color: #1e293b;
}

.info-hapus {
  margin: 1rem 0 0 0;
  font-size: 0.85rem;
  color: #64748b;
}

.info-hapus strong {
  color: #1e293b;
}
</style>
