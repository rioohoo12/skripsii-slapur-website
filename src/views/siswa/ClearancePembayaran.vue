<template>
  <div class="clearance-pembayaran-page" :class="themeClass">
    <div class="chat-modal-window custom-chat-payment">
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="bot-info">
          <div class="bot-avatar">🤖</div>
          <div>
            <h3>Asisten Pembayaran SLAPUR</h3>
            <span class="online-status">● Berinteraksi Langsung</span>
          </div>
        </div>
        <div class="header-actions">
          <button @click="loadData" class="action-header-btn reset-btn" title="Refresh Status">
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
            Halo! 👋 Saya Asisten Pembayaran. Berikut adalah detail tagihan pendaftaran Anda:
          </div>
        </div>

        <!-- Detail Pembayaran Message -->
        <div class="chat-message bot">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble detail-bubble">
            <div class="detail-title">Rincian Pembayaran Pendaftaran (60%)</div>
            <table class="detail-table">
              <tbody>
                <tr>
                  <td>Status</td>
                  <td>
                    <span class="status-badge" :class="statusClass">{{ statusLabel }}</span>
                  </td>
                </tr>
                <tr>
                  <td>Total Tagihan</td>
                  <td><strong>{{ payment ? formatRp(payment.total_tagihan) : 'Rp 5.000.000' }}</strong></td>
                </tr>
                <tr>
                  <td>Nominal Dibayar</td>
                  <td><strong>{{ payment ? formatRp(payment.nominal_dibayar) : 'Rp 3.000.000' }}</strong></td>
                </tr>
                <tr v-if="payment?.verified_at">
                  <td>Diverifikasi Pada</td>
                  <td>{{ formatDate(payment.verified_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rekening Message if Belum Bayar -->
        <div class="chat-message bot" v-if="statusPembayaran === 'belum_bayar' || statusPembayaran === 'ditolak'">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble">
            Silakan lakukan transfer ke salah satu rekening sekolah resmi berikut:
            
            <div class="bank-list mt-3">
              <div class="bank-item">
                <div class="bank-logo">BCA</div>
                <div class="bank-details">
                  <div class="bank-name">Bank BCA</div>
                  <div class="rek-number">1234 5678 90</div>
                  <div class="rek-name">A.n. Sekolah SLA Purwodadi</div>
                </div>
              </div>
              <div class="bank-item mt-2">
                <div class="bank-logo">BNI</div>
                <div class="bank-details">
                  <div class="bank-name">Bank BNI</div>
                  <div class="rek-number">0987 654 321</div>
                  <div class="rek-name">A.n. Sekolah SLA Purwodadi</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action / Status Message -->
        <div class="chat-message bot">
          <div class="msg-avatar">🤖</div>
          <div class="msg-bubble" v-if="statusPembayaran === 'belum_bayar' || statusPembayaran === 'ditolak'">
            Jika Anda sudah mentransfer, silakan upload bukti pembayaran (foto/PDF) menggunakan tombol di bawah ini.
          </div>
          <div class="msg-bubble" v-else-if="statusPembayaran === 'menunggu' || statusPembayaran === 'pending'">
            ⏳ Bukti pembayaran Anda sedang diverifikasi oleh sistem. Silakan tunggu beberapa saat dan klik refresh.
            <div class="mt-2" v-if="payment?.bukti_path">
              <a :href="`/storage/${payment.bukti_path}`" target="_blank" class="btn-secondary">Lihat Bukti yang Diupload</a>
            </div>
          </div>
          <div class="msg-bubble success-bubble" v-else-if="statusPembayaran === 'terverifikasi'">
            ✅ Selamat! Pembayaran pendaftaran Anda telah berhasil diverifikasi oleh sistem. Anda bisa melanjutkan ke langkah pendaftaran berikutnya.
          </div>
        </div>

        <!-- User Sent File Message -->
        <div class="chat-message user" v-if="selectedFile">
          <div class="msg-bubble user-bubble">
            📎 <strong>File dipilih:</strong> {{ selectedFile.name }}
          </div>
        </div>

      </div>

      <!-- Quick Actions / Input Area -->
      <div class="chat-input-area" v-if="statusPembayaran === 'belum_bayar' || statusPembayaran === 'ditolak'">
        <div class="upload-controls">
          <input type="file" id="bukti_pembayaran" accept="image/*,.pdf" @change="onFileChange" class="hidden-input" ref="fileInput" />
          <button class="opt-btn btn-pilih" @click="$refs.fileInput.click()" :disabled="loadingPay">
            📎 Pilih File Bukti
          </button>
          
          <button class="opt-btn btn-kirim" :disabled="loadingPay || !selectedFile" @click="handleUpload">
            <span v-if="loadingPay" class="spinner"></span>
            {{ loadingPay ? 'Mengupload...' : '🚀 Kirim Bukti' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { pendaftaranApi } from '@/api/pendaftaran.js';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const payment = ref(null);
const loadError = ref('');
const loadingPay = ref(false);
const messagesContainer = ref(null);

const statusPembayaran = computed(() => payment.value?.status ?? 'belum_bayar');
const statusLabel = computed(() => {
  if (statusPembayaran.value === 'terverifikasi') return 'Berhasil Bayar';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'Menunggu Verifikasi';
  return 'Belum Dibayar';
});
const statusClass = computed(() => {
  if (statusPembayaran.value === 'terverifikasi') return 'status-ok';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'status-pending';
  return 'status-no';
});

const selectedFile = ref(null);
const fileInput = ref(null);

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}

function onFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
    scrollToBottom();
  }
}

function formatRp(n) {
  if (n == null || n === '') return '—';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch {
    return iso;
  }
}

async function loadData() {
  try {
    const data = await pendaftaranApi.getStatus();
    payment.value = data.payment ?? null;
    scrollToBottom();
  } catch (e) {
    loadError.value = e.message || 'Gagal memuat status';
  }
}

async function handleUpload() {
  if (!selectedFile.value) {
    alert('Pilih file bukti pembayaran terlebih dahulu!');
    return;
  }
  loadingPay.value = true;
  try {
    const res = await pendaftaranApi.uploadBuktiPembayaran(selectedFile.value);
    alert(res.message || 'Berhasil diupload');
    selectedFile.value = null;
    loadData();
  } catch (e) {
    alert(e.message || 'Gagal mengupload bukti pembayaran.');
  } finally {
    loadingPay.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.clearance-pembayaran-page {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 1rem 0 3rem 0;
  font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
}

.custom-chat-payment {
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

/* Detail Table */
.detail-title {
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  border-bottom: 1px dashed #cbd5e1;
  padding-bottom: 0.5rem;
}

.detail-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.detail-table td {
  padding: 0.4rem 0;
  vertical-align: top;
}

.detail-table td:first-child {
  color: #64748b;
  width: 45%;
}

.detail-table td:last-child {
  color: #0f1e3c;
  text-align: right;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
}
.status-badge.status-pending { background: #fef3c7; color: #92400e; }
.status-badge.status-ok { background: #d1fae5; color: #065f46; }
.status-badge.status-no { background: #fee2e2; color: #991b1b; }

/* Bank Info */
.bank-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.bank-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #f1f5f9;
  padding: 0.75rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.bank-logo {
  background: #fff;
  border: 1px solid #cbd5e1;
  color: #0f1e3c;
  font-weight: 800;
  font-size: 0.9rem;
  width: 50px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  letter-spacing: 1px;
}

.bank-details {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.bank-name { font-weight: 700; color: #1e293b; font-size: 0.85rem; }
.rek-number {
  font-family: monospace; font-size: 1.05rem; font-weight: 700; color: #0f766e;
}
.theme-perempuan .rek-number { color: #6d28d9; }
.rek-name { font-size: 0.75rem; color: #64748b; }

/* Input Area */
.chat-input-area {
  padding: 1rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
}

.upload-controls {
  display: flex;
  gap: 0.75rem;
}

.hidden-input {
  display: none;
}

.opt-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border-radius: 999px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
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

.btn-secondary {
  display: inline-block;
  padding: 0.4rem 0.8rem;
  background: #fff;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.8rem;
  color: #334155;
  text-decoration: none;
  font-weight: 600;
}
.btn-secondary:hover { background: #f8fafc; }

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 0.75rem; }
</style>

