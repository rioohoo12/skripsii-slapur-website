<template>
  <div class="admin-dokumen-page" :class="themeClass">
    <div class="chat-modal-window custom-chat-dokumen">
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="bot-info">
          <div class="bot-avatar">🤖</div>
          <div>
            <h3>Asisten Dokumen SLAPUR</h3>
            <span class="online-status">● Berinteraksi Langsung</span>
          </div>
        </div>
        <div class="header-actions">
          <button @click="loadDokumen" class="action-header-btn reset-btn" title="Refresh Status">
            🔄 Refresh
          </button>
        </div>
      </div>

      <!-- Messages Scroll Area -->
      <div class="chat-messages-area" ref="messagesContainer">
        
        <!-- Welcome & Info Message -->
        <div class="chat-message bot">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble">
            Halo! 👋 Saya Asisten Dokumen. Silakan unggah dokumen pendaftaran Anda. Dokumen yang diunggah akan langsung diverifikasi secara otomatis oleh AI kami.
          </div>
        </div>

        <!-- Persyaratan & Status -->
        <div class="chat-message bot">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble detail-bubble">
            <div class="detail-title">Persyaratan Dokumen</div>
            <ul class="req-list">
              <li>
                <span>Akte Kelahiran</span>
                <span class="req-badge" :class="reqStatus.akte_kelahiran ? 'ok' : 'pending'">{{ reqStatus.akte_kelahiran ? '✓ Sudah' : '✗ Belum' }}</span>
              </li>
              <li>
                <span>Kartu Keluarga (KK)</span>
                <span class="req-badge" :class="reqStatus.kk ? 'ok' : 'pending'">{{ reqStatus.kk ? '✓ Sudah' : '✗ Belum' }}</span>
              </li>
              <li>
                <span>Pas Foto</span>
                <span class="req-badge" :class="reqStatus.pas_foto ? 'ok' : 'pending'">{{ reqStatus.pas_foto ? '✓ Sudah' : '✗ Belum' }}</span>
              </li>
              <li>
                <span>Raport (Pindahan)</span>
                <span class="req-badge" :class="reqStatus.raport ? 'ok' : 'pending'">{{ reqStatus.raport ? '✓ Sudah' : '✗ Belum' }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Dokumen Terunggah -->
        <div class="chat-message bot" v-if="daftarDokumen.length > 0">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble detail-bubble">
            <div class="detail-title">Dokumen Terunggah</div>
            <div class="doc-list">
              <div v-for="(doc, i) in daftarDokumen" :key="i" class="doc-item">
                <div class="doc-info">
                  <span class="doc-name">{{ doc.tipe }}</span>
                  <span class="doc-meta">{{ doc.tanggal }}</span>
                </div>
                <span class="status-badge" :class="doc.statusClass">{{ doc.status }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action / Status Message -->
        <div class="chat-message bot">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble">
            Pilih jenis dokumen dan klik tombol upload di bawah untuk mengunggah file. (Format: PDF/JPG/PNG/WebP).
            <br/><br/>
            <button class="btn-chat-verify" @click="openChatbotVerification">
              🤖 Butuh Bantuan AI untuk mengisi dokumen?
            </button>
          </div>
        </div>

        <!-- User Sent File Message (Preview before upload) -->
        <div class="chat-message user" v-if="form.file || form.tipeDokumen">
          <div class="msg-bubble user-bubble">
            <div v-if="form.tipeDokumen"><strong>Jenis:</strong> {{ labelJenis(form.tipeDokumen) }}</div>
            <div v-if="form.file">📎 <strong>File:</strong> {{ form.fileName }}</div>
          </div>
        </div>

        <!-- Error/Success Messages -->
        <div class="chat-message bot" v-if="errorMsg || successMsg">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble" :class="errorMsg ? 'error-bubble' : 'success-bubble'">
            {{ errorMsg || successMsg }}
          </div>
        </div>

      </div>

      <!-- Quick Actions / Input Area -->
      <div class="chat-input-area">
        <form class="upload-controls" @submit.prevent="tambahDokumen">
          <select v-model="form.tipeDokumen" class="select-jenis" required>
            <option value="">— Pilih Jenis —</option>
            <option value="kk">Kartu Keluarga (KK)</option>
            <option value="akte_kelahiran">Akte Kelahiran</option>
            <option value="ijazah">Ijazah</option>
            <option value="pas_foto">Pas Foto</option>
            <option value="raport">Rapor</option>
            <option value="lainnya">Lainnya</option>
          </select>
          
          <input type="file" ref="fileInputRef" accept=".pdf,.jpg,.jpeg,.png,.webp" @change="onFileSelect" class="hidden-input" />
          <button type="button" class="opt-btn btn-pilih" @click="$refs.fileInputRef.click()" :disabled="uploading">
            📎 File
          </button>
          
          <button type="submit" class="opt-btn btn-kirim" :disabled="uploading || !form.file || !form.tipeDokumen">
            <span v-if="uploading" class="spinner"></span>
            {{ uploading ? 'Wait...' : '🚀 Kirim' }}
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { pendaftaranApi } from '@/api/pendaftaran';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const form = reactive({
  tipeDokumen: '',
  fileName: '',
  file: null,
});

const fileInputRef = ref(null);
const messagesContainer = ref(null);
const daftarDokumen = ref([]);
const uploading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const reqStatus = computed(() => {
  const uploadedJenis = new Set((daftarDokumen.value || []).map((d) => d?.jenisKey).filter(Boolean));
  return {
    akte_kelahiran: uploadedJenis.has('akte_kelahiran'),
    kk: uploadedJenis.has('kk'),
    pas_foto: uploadedJenis.has('pas_foto'),
    raport: uploadedJenis.has('raport'),
  };
});

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}

function onFileSelect(e) {
  const f = e.target.files?.[0];
  form.file = f || null;
  form.fileName = f ? f.name : '';
  scrollToBottom();
}

function labelJenis(jenis) {
  const labels = {
    kk: 'Kartu Keluarga (KK)',
    akte_kelahiran: 'Akte Kelahiran',
    ijazah: 'Ijazah',
    pas_foto: 'Pas Foto',
    raport: 'Rapor',
    lainnya: 'Lainnya',
  };
  return labels[jenis] || jenis;
}

function statusLabel(status) {
  if (status === 'terverifikasi') return { text: 'Terverifikasi', cls: 'status-ok' };
  return { text: 'Menunggu verifikasi', cls: 'status-pending' };
}

function fmtDate(iso) {
  if (!iso) return '-';
  try {
    return new Date(iso).toLocaleDateString('id-ID');
  } catch {
    return '-';
  }
}

async function loadDokumen() {
  try {
    const res = await pendaftaranApi.getDokumen();
    const docs = Array.isArray(res.documents) ? res.documents : [];
    daftarDokumen.value = docs.map((d) => {
      const st = statusLabel(d.status);
      return {
        id: d.id,
        jenisKey: d.jenis,
        tipe: labelJenis(d.jenis),
        tanggal: fmtDate(d.updated_at || d.created_at),
        status: st.text,
        statusClass: st.cls,
        fileUrl: d.file_url,
      };
    });
    scrollToBottom();
  } catch (e) {
    // diamkan, tetap tampil kosong
  }
}

async function tambahDokumen() {
  errorMsg.value = '';
  successMsg.value = '';
  if (!form.tipeDokumen || !form.file || uploading.value) return;

  uploading.value = true;
  try {
    await pendaftaranApi.uploadDokumen(form.tipeDokumen, form.file);
    successMsg.value = '✅ Dokumen berhasil diunggah dan sedang diproses.';
    await loadDokumen();
    form.tipeDokumen = '';
    form.fileName = '';
    form.file = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
  } catch (e) {
    errorMsg.value = '❌ ' + (e.message || 'Gagal upload dokumen. Silakan coba lagi.');
  } finally {
    uploading.value = false;
    scrollToBottom();
  }
}

function openChatbotVerification() {
  window.dispatchEvent(new CustomEvent('open-chat', { detail: 'saya mau upload dokumen' }));
}

onMounted(() => {
  loadDokumen();
});
</script>

<style scoped>
.admin-dokumen-page {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 1rem 0 3rem 0;
  font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
}

.custom-chat-dokumen {
  width: 100%;
  max-width: 600px;
  height: min(85vh, 750px);
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Chat Header */
.chat-header {
  background: linear-gradient(135deg, #0d6e59, #115e59);
  color: #ffffff;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.theme-perempuan .chat-header {
  background: linear-gradient(135deg, #5b21b6, #4c1d95);
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
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  color: #ffffff;
}

.online-status {
  font-size: 0.75rem;
  color: #5eead4;
}

.theme-perempuan .online-status {
  color: #d8b4fe;
}

.header-actions {
  display: flex;
  align-items: center;
}

.action-header-btn {
  background: rgba(255, 255, 255, 0.18);
  border: none;
  color: #ffffff;
  padding: 0.4rem 0.85rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.action-header-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Messages Area */
.chat-messages-area {
  flex: 1;
  padding: 1.25rem;
  overflow-y: auto;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.chat-message {
  display: flex;
  gap: 0.75rem;
  max-width: 90%;
}

.chat-message.bot {
  align-self: flex-start;
}

.chat-message.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.msg-avatar {
  width: 32px;
  height: 32px;
  background: #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.msg-bubble {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 0.85rem 1rem;
  border-radius: 0 16px 16px 16px;
  font-size: 0.95rem;
  color: #334155;
  line-height: 1.5;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}

.chat-message.user .user-bubble {
  background: #0d6e59;
  color: #ffffff;
  border-radius: 16px 0 16px 16px;
  border: none;
}

.theme-perempuan .chat-message.user .user-bubble {
  background: #6d28d9;
}

.detail-bubble {
  padding: 1rem;
  width: 100%;
}

.success-bubble {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #065f46;
}

.error-bubble {
  background: #fef2f2;
  border-color: #fecaca;
  color: #991b1b;
}

/* Lists and Details inside bubbles */
.detail-title {
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  border-bottom: 1px dashed #cbd5e1;
  padding-bottom: 0.5rem;
}

.req-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.req-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}

.req-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.1rem 0.5rem;
  border-radius: 4px;
}

.req-badge.ok { background: #d1fae5; color: #065f46; }
.req-badge.pending { background: #f1f5f9; color: #64748b; }

.doc-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.doc-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.doc-info {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.doc-name { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
.doc-meta { font-size: 0.75rem; color: #64748b; }

.status-badge {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}
.status-badge.status-ok { background: #d1fae5; color: #065f46; }
.status-badge.status-pending { background: #fef3c7; color: #92400e; }

/* Input Area */
.chat-input-area {
  padding: 1rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
}

.upload-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.select-jenis {
  flex: 1.5;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 0.85rem;
  font-family: inherit;
  outline: none;
}

.hidden-input {
  display: none;
}

.opt-btn {
  flex: 1;
  padding: 0.75rem 0.5rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition: all 0.2s;
  border: none;
}

.btn-pilih {
  background: #f1f5f9;
  color: #475569;
}
.btn-pilih:hover { background: #e2e8f0; }

.btn-kirim {
  background: #0d6e59;
  color: #ffffff;
}
.btn-kirim:hover:not(:disabled) { background: #0f766e; }
.theme-perempuan .btn-kirim { background: #6d28d9; }
.theme-perempuan .btn-kirim:hover:not(:disabled) { background: #5b21b6; }

.opt-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-chat-verify {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #3b82f6;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
  width: 100%;
}

.btn-chat-verify:hover {
  background: #2563eb;
  transform: translateY(-1px);
}
</style>
