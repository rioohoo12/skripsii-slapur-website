<template>
  <div class="keuangan-page">
    <div class="keuangan-main">
      <!-- Tabel Informasi Keuangan -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-tabel">
        <template #header>Informasi Keuangan</template>
        <div class="table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="num">Debit</th>
                <th class="num">Kredit</th>
                <th class="num">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in transaksi" :key="i" :class="{ 'row-credit': row.saldoCr }">
                <td class="date">{{ row.tanggal }}</td>
                <td class="desc">{{ row.keterangan }}</td>
                <td class="num">{{ row.debit ? formatRp(row.debit) : '—' }}</td>
                <td class="num">{{ row.kredit ? formatRp(row.kredit) : '—' }}</td>
                <td class="num saldo">
                  {{ formatRp(row.saldo) }}{{ row.saldoCr ? ' Cr' : '' }}
                </td>
              </tr>
              <tr v-if="!transaksi.length" class="empty-row">
                <td colspan="5">Belum ada data transaksi</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="footer-row">
                <td colspan="4">Saldo Akhir</td>
                <td class="num saldo-akhir">{{ transaksi.length ? formatRp(saldoAkhir) : '—' }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </PageCard>
    </div>

    <!-- Sidebar kanan -->
    <aside class="keuangan-sidebar">
      <!-- Pilih Tanggal -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-filter">
        <template #header>Pilih Tanggal</template>
        <div class="filter-fields">
          <div class="field">
            <label>Tanggal Mulai</label>
            <input v-model="tanggalMulai" type="date" class="input-date" />
          </div>
          <div class="field">
            <label>Tanggal Akhir</label>
            <input v-model="tanggalAkhir" type="date" class="input-date" />
          </div>
          <button type="button" class="btn-pilih" :class="themeClass" @click="applyFilter">
            Pilih Tanggal
          </button>
        </div>
      </PageCard>

      <!-- Saldo Akhir - kartu besar -->
      <div class="card-saldo" :class="themeClass">
        <div class="saldo-icon">
          <span class="saldo-symbol">Rp</span>
        </div>
        <p class="saldo-value">{{ transaksi.length ? formatRp(Math.abs(saldoAkhir)) : '—' }}</p>
        <p class="saldo-label">Saldo Akhir</p>
      </div>

      <!-- Pending dari BNI -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-pending">
        <template #header>
          <span>Pending dari Rekening</span>
          <button type="button" class="toggle-btn" :aria-expanded="pendingOpen" @click="pendingOpen = !pendingOpen">
            <span class="arrow" :class="{ open: pendingOpen }">▼</span>
          </button>
        </template>
        <div v-show="pendingOpen" class="pending-content">
          <table v-if="pendingList.length" class="pending-table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in pendingList" :key="i">
                <td>{{ i + 1 }}</td>
                <td>{{ p.tanggal }}</td>
                <td class="num">{{ formatRp(p.jumlah) }}</td>
              </tr>
            </tbody>
          </table>
          <p v-else class="empty-pending">Belum ada pembayaran pending</p>
        </div>
      </PageCard>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const tanggalMulai = ref('2026-01-01');
const tanggalAkhir = ref('2026-06-30');
const pendingOpen = ref(true);

// Data kosong – isi dari API nanti
const transaksi = ref([]);
const pendingList = ref([]);

const saldoAkhir = computed(() => {
  const last = transaksi.value[transaksi.value.length - 1];
  return last ? last.saldo : 0;
});

function formatRp(n) {
  if (n == null) return '—';
  return new Intl.NumberFormat('id-ID', {
    style: 'decimal',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(n);
}

function applyFilter() {
  // Nanti panggil API dengan tanggalMulai, tanggalAkhir
  console.log('Filter:', tanggalMulai.value, tanggalAkhir.value);
}
</script>

<style scoped>
.keuangan-page {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 1000px) {
  .keuangan-page {
    grid-template-columns: 1fr;
  }
}

.keuangan-main {
  min-width: 0;
}

.card-tabel :deep(.page-card-body) {
  padding: 0;
  overflow-x: auto;
}

.table-wrap {
  overflow-x: auto;
}

.finance-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.finance-table th,
.finance-table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.finance-table th {
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
  white-space: nowrap;
}

.finance-table th.num,
.finance-table td.num {
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.finance-table tbody tr:hover {
  background: #fafafa;
}

.finance-table .empty-row td {
  text-align: center;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

.finance-table .date {
  color: #475569;
  white-space: nowrap;
}

.finance-table .desc {
  color: #1e293b;
  max-width: 280px;
}

.finance-table .saldo {
  font-weight: 600;
  color: #1e293b;
}

.finance-table .row-credit .saldo {
  color: #059669;
}

.finance-table tfoot .footer-row {
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  font-weight: 700;
  color: #1e293b;
}

.finance-table .saldo-akhir {
  font-size: 1.05rem;
  color: #0f766e;
}

.theme-perempuan .finance-table .row-credit .saldo {
  color: #6d28d9;
}

.theme-perempuan .finance-table .saldo-akhir {
  color: #5b21b6;
}

/* Sidebar */
.keuangan-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card-filter :deep(.page-card-body) {
  padding: 1.25rem;
}

.filter-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filter-fields .field label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.35rem;
}

.input-date {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.9rem;
  color: #1e293b;
}

.input-date:focus {
  outline: none;
  border-color: #0f766e;
  box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
}

.theme-perempuan .input-date:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.15);
}

.btn-pilih {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s;
  margin-top: 0.25rem;
}

.btn-pilih.theme-laki-laki {
  background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);
}

.btn-pilih.theme-perempuan {
  background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
  color: #fff;
  box-shadow: 0 2px 8px rgba(124, 58, 237, 0.35);
}

.btn-pilih:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Kartu Saldo Akhir */
.card-saldo {
  padding: 1.75rem;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  border: 1px solid transparent;
}

.card-saldo.theme-laki-laki {
  background: linear-gradient(160deg, #ccfbf1 0%, #99f6e4 50%, #5eead4 100%);
  border-color: rgba(255, 255, 255, 0.5);
}

.card-saldo.theme-perempuan {
  background: linear-gradient(160deg, #ede9fe 0%, #ddd6fe 50%, #c4b5fd 100%);
  border-color: rgba(255, 255, 255, 0.5);
}

.saldo-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.card-saldo.theme-laki-laki .saldo-icon {
  color: #0f766e;
}

.card-saldo.theme-perempuan .saldo-icon {
  color: #5b21b6;
}

.saldo-symbol {
  font-size: 1.25rem;
  font-weight: 800;
}

.saldo-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.saldo-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  margin: 0;
}

/* Pending */
.card-pending :deep(.page-card-header) {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.toggle-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: inherit;
}

.toggle-btn .arrow {
  font-size: 0.7rem;
  display: inline-block;
  transition: transform 0.2s;
}

.toggle-btn .arrow.open {
  transform: rotate(180deg);
}

.pending-table {
  width: 100%;
  font-size: 0.85rem;
  border-collapse: collapse;
}

.pending-table th,
.pending-table td {
  padding: 0.5rem 0.75rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.pending-table th {
  color: #64748b;
  font-weight: 600;
}

.pending-table td.num {
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.empty-pending {
  margin: 0;
  padding: 1rem 0;
  font-size: 0.9rem;
  color: #94a3b8;
  text-align: center;
}
</style>
