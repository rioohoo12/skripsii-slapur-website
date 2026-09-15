<template>
  <div class="clearance-pembayaran-page" :class="themeClass">
    <h2 class="page-heading">Pembayaran Pendaftaran</h2>
    <p class="page-subtitle">Status pembayaran 60% pendaftaran dan verifikasi (diverifikasi otomatis via Midtrans).</p>

    <div class="content-layout">
      <main class="content-main">
        <PageCard :jenis-kelamin="jenisKelamin" class="card-status">
          <template #header>Status Pembayaran Pendaftaran</template>
          <div class="status-body">
            <div class="status-badge" :class="[themeClass, statusClass]">
              {{ statusLabel }}
            </div>
            <p class="status-desc">{{ statusDesc }}</p>
            
            <div v-if="statusPembayaran !== 'terverifikasi'" class="payment-actions">
              <button class="btn-pay" :disabled="loadingPay" @click="handleBayar">
                <span v-if="loadingPay" class="spinner"></span>
                {{ loadingPay ? 'Memproses...' : 'Bayar Sekarang (Midtrans)' }}
              </button>
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
            <li>Lakukan pembayaran pendaftaran sesuai nominal yang ditetapkan.</li>
            <li>Pembayaran menggunakan Virtual Account, GoPay, OVO, dll via Midtrans.</li>
            <li>Status akan terupdate otomatis setelah pembayaran berhasil.</li>
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
  if (statusPembayaran.value === 'terverifikasi') return 'Pembayaran pendaftaran Anda telah berhasil terverifikasi.';
  if (statusPembayaran.value === 'menunggu' || statusPembayaran.value === 'pending') return 'Selesaikan pembayaran Anda melalui Midtrans.';
  return 'Silakan klik Bayar Sekarang untuk menyelesaikan pembayaran pendaftaran.';
});

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

async function handleBayar() {
  loadingPay.value = true;
  try {
    const res = await paymentApi.createTransaction();
    if (res.snap_token) {
      window.snap.pay(res.snap_token, {
        onSuccess: function(result){
          loadData();
        },
        onPending: function(result){
          loadData();
        },
        onError: function(result){
          alert('Pembayaran gagal atau terjadi kesalahan.');
        },
        onClose: function(){
          loadData();
        }
      });
    }
  } catch (e) {
    alert(e.message || 'Gagal memulai pembayaran.');
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
  color: #64748b;
  margin: 0;
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
</style>
