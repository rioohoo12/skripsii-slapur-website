<template>
  <div class="clearance-pembayaran-page" :class="themeClass">
    <h2 class="page-heading">Pembayaran Pendaftaran</h2>
    <p class="page-subtitle">Status pembayaran pendaftaran (Diverifikasi Otomatis oleh Sistem AI secara Instan).</p>

    <div class="content-layout">
      <main class="content-main">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-status">
          <template #header>Status Pembayaran Pendaftaran</template>
          <div class="status-body">
            <div class="status-badge" :class="[themeClass, statusClass]">
              {{ statusLabel }}
            </div>
            <p class="status-desc">{{ statusDesc }}</p>
            
            <div v-if="statusPembayaran === 'belum_bayar' || statusPembayaran === 'ditolak'" class="payment-upload flex flex-col gap-3">
              <label class="upload-label" for="bukti_pembayaran">Upload Bukti Transfer (Gambar/PDF)</label>
              <input type="file" id="bukti_pembayaran" accept="image/*" @change="onFileChange" :disabled="loadingPay" class="file-input" />
              <button class="btn-pay" :disabled="loadingPay || !selectedFile" @click="handleUpload">
                <span v-if="loadingPay" class="spinner"></span>
                {{ loadingPay ? 'Mengupload...' : 'Upload & Simpan Bukti' }}
              </button>
              
              <div class="mt-2 text-center text-sm font-semibold text-gray-500">ATAU</div>
              
              <button class="btn-chat-verify" @click="openChatbotVerification">
                🤖 Verifikasi Instan via Chatbot AI
              </button>
            </div>
            
            <div v-else-if="statusPembayaran === 'menunggu' || statusPembayaran === 'pending'" class="payment-waiting">
              <p>Bukti pembayaran telah diupload dan sedang diverifikasi oleh sistem.</p>
              <a v-if="payment?.bukti_path" :href="`/storage/${payment.bukti_path}`" target="_blank" class="btn-secondary">Lihat Bukti yang Diupload</a>
            </div>
          </div>
        </PageCard>

        <PageCard :jenis-kelamin="jenisKelamin" class="card-rekening" v-if="statusPembayaran === 'belum_bayar' || statusPembayaran === 'ditolak'">
          <template #header>Rekening Tujuan Transfer</template>
          <div class="rekening-body">
            <p class="rekening-desc">Silakan transfer biaya pendaftaran ke rekening resmi sekolah berikut:</p>
            <div class="bank-list">
              <div class="bank-item">
                <div class="bank-logo">BCA</div>
                <div class="bank-details">
                  <div class="bank-name">Bank BCA</div>
                  <div class="rek-number">1234 5678 90</div>
                  <div class="rek-name">A.n. Sekolah SLA Purwodadi</div>
                </div>
              </div>
              <div class="bank-item">
                <div class="bank-logo">BNI</div>
                <div class="bank-details">
                  <div class="bank-name">Bank BNI</div>
                  <div class="rek-number">0987 654 321</div>
                  <div class="rek-name">A.n. Sekolah SLA Purwodadi</div>
                </div>
              </div>
            </div>
            <div class="rekening-note">
              <em>* Pastikan untuk menyimpan struk atau bukti screenshot setelah melakukan transfer.</em>
            </div>
          </div>
        </PageCard>

        <PageCard :jenis-kelamin="jenisKelamin" class="card-detail">
          <template #header>Detail Pembayaran</template>
          <div class="detail-table-wrap">
            <table class="detail-table">
              <tbody>
                <tr>
                  <td class="label">Jenis</td>
                  <td class="value">Pembayaran Pendaftaran (60%)</td>
                </tr>
                <tr>
                  <td class="label">Total Tagihan</td>
                  <td class="value">{{ payment ? formatRp(payment.total_tagihan) : 'Rp 5.000.000' }}</td>
                </tr>
                <tr>
                  <td class="label">Nominal Dibayar (60%)</td>
                  <td class="value">{{ payment ? formatRp(payment.nominal_dibayar) : 'Rp 3.000.000' }}</td>
                </tr>
                <tr>
                  <td class="label">Verifikasi</td>
                  <td class="value">{{ payment?.verified_at ? formatDate(payment.verified_at) : '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </PageCard>
      </main>

      <aside class="content-sidebar">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-info">
          <template #header>Informasi</template>
          <ul class="info-list">
            <li>Lakukan pembayaran pendaftaran sesuai nominal yang ditetapkan melalui transfer bank.</li>
            <li>Setelah transfer, upload foto atau scan bukti transfer di form yang tersedia.</li>
            <li>Sistem AI kami akan memverifikasi bukti pembayaran Anda secara instan (24/7).</li>
          </ul>
        </PageCard>

        <PageCard :jenis-kelamin="jenisKelamin" class="card-tahun">
          <template #header>Tahun Ajaran</template>
          <select v-model="tahunSemester" class="select-tahun">
            <option value="2025/2026 - GENAP">2025/2026 - GENAP</option>
            <option value="2025/2026 - GANJIL">2025/2026 - GANJIL</option>
            <option value="2024/2025 - GENAP">2024/2025 - GENAP</option>
          </select>
        </PageCard>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import PageCard from '@/components/PageCard.vue';
import { pendaftaranApi } from '@/api/pendaftaran.js';
import { paymentApi } from '@/api/payment.js';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const tahunSemester = ref('2025/2026 - GENAP');
const payment = ref(null);
const loadError = ref('');
const loadingPay = ref(false);

const statusPembayaran = computed(() => payment.value?.status ?? 'belum_bayar');
const statusLabel = computed(() => {
  if (statusPembayaran.value === 'terverifikasi') return 'Berhasil Bayar';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'Menunggu Pembayaran';
  return 'Belum Dibayar';
});
const statusClass = computed(() => {
  if (statusPembayaran.value === 'terverifikasi') return 'status-ok';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'status-pending';
  return 'status-no';
});
const statusDesc = computed(() => {
  if (statusPembayaran.value === 'terverifikasi') return 'Pembayaran pendaftaran Anda telah berhasil diverifikasi oleh AI.';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'Menunggu verifikasi pembayaran oleh sistem AI...';
  if (statusPembayaran.value === 'ditolak') return 'Bukti pembayaran ditolak oleh sistem AI, mohon periksa dan upload ulang.';
  return 'Silakan upload bukti pembayaran pendaftaran (transfer bank).';
});

const selectedFile = ref(null);

function onFileChange(e) {
  const file = e.target.files[0];
  if (file) {
    selectedFile.value = file;
  }
}

function formatRp(n) {
  if (n == null || n === '') return '—';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
}
function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch {
    return iso;
  }
}

async function loadData() {
  try {
    const data = await pendaftaranApi.getStatus();
    payment.value = data.payment ?? null;
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

function openChatbotVerification() {
  window.dispatchEvent(new CustomEvent('open-chat', { detail: 'saya mau bayar' }));
}

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.clearance-pembayaran-page {
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

.content-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .content-layout {
    grid-template-columns: 1fr;
  }
}

.content-main {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-status :deep(.page-card-body),
.card-detail :deep(.page-card-body) {
  padding: 1.5rem;
}

.status-body {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1rem;
}

.status-badge {
  padding: 0.6rem 1.25rem;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
}

.status-badge.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.status-ok {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-no {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.theme-laki-laki:not([class*="status-"]) {
  background: #ccfbf1;
  color: #0f766e;
}

.status-badge.theme-perempuan:not([class*="status-"]) {
  background: #ede9fe;
  color: #5b21b6;
}

.status-desc {
  font-size: 0.95rem;
  color: #475569;
  line-height: 1.4;
  margin: 0;
}

/* Rekening Section */
.card-rekening :deep(.page-card-body) {
  padding: 1.5rem;
}

.rekening-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.rekening-desc {
  font-size: 0.95rem;
  color: #334155;
  margin: 0;
  font-weight: 500;
}

.bank-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.bank-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  background: #f8fafc;
  padding: 1rem 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.bank-logo {
  background: #fff;
  border: 1px solid #cbd5e1;
  color: #0f1e3c;
  font-weight: 800;
  font-size: 1.1rem;
  width: 60px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  letter-spacing: 1px;
}

.bank-details {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.bank-name {
  font-weight: 700;
  color: #1e293b;
  font-size: 0.95rem;
}

.rek-number {
  font-family: monospace;
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f766e;
  letter-spacing: 1.5px;
}

.theme-perempuan .rek-number {
  color: #6d28d9;
}

.rek-name {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.rekening-note {
  font-size: 0.85rem;
  color: #94a3b8;
  margin-top: 0.5rem;
}

.payment-upload {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  max-width: 400px;
}

.upload-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
}

.file-input {
  padding: 0.5rem;
  border: 1px dashed #cbd5e1;
  border-radius: 8px;
  background: #f8fafc;
  cursor: pointer;
}

.payment-waiting {
  background: #fef3c7;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid #fde68a;
  color: #92400e;
  font-size: 0.95rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-secondary {
  display: inline-block;
  text-align: center;
  background: #fff;
  border: 1px solid #d1d5db;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #374151;
  text-decoration: none;
  cursor: pointer;
}
.btn-secondary:hover {
  background: #f9fafb;
}

.detail-table-wrap {
  overflow-x: auto;
}

.detail-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

.detail-table td {
  padding: 0.75rem 0.5rem 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}

.detail-table tr:last-child td {
  border-bottom: none;
}

.detail-table .label {
  color: #64748b;
  font-weight: 500;
  width: 40%;
}

.detail-table .value {
  color: #1e293b;
  font-weight: 600;
}

.content-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-info :deep(.page-card-body),
.card-tahun :deep(.page-card-body) {
  padding: 1.25rem;
}

.info-list {
  margin: 0;
  padding-left: 1.2rem;
  color: #475569;
  font-size: 0.9rem;
  line-height: 1.7;
}

.info-list li {
  margin-bottom: 0.5rem;
}

.select-tahun {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}

.select-tahun:focus {
  outline: none;
  border-color: #0f766e;
}

.theme-perempuan .select-tahun:focus {
  border-color: #7c3aed;
}

.payment-actions {
  margin-top: 1rem;
}

.btn-pay {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pay:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-pay:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.theme-laki-laki .btn-pay {
  background: #0f766e;
}

.theme-perempuan .btn-pay {
  background: #7c3aed;
}

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
.btn-chat-verify {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.btn-chat-verify:hover {
  background: #2563eb;
  transform: translateY(-1px);
}
</style>
