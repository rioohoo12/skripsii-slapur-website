<template>
  <div class="tagihan-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Tagihan & Pembayaran Siswa</h1>
        <p class="page-subtitle">
          Informasi kewajiban pembayaran sekolah, status pelunasan, dan riwayat verifikasi administrasi.
        </p>
      </div>
      <div class="header-actions">
        <button @click="fetchTagihan" class="refresh-btn" :disabled="loading" title="Refresh data">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'spin-icon': loading }"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
          <span>Refresh Data</span>
        </button>
      </div>
    </div>

    <!-- Banner Keterangan Status Keseluruhan -->
    <div class="status-banner" :class="bannerClass">
      <div class="banner-icon-box">
        <span v-if="summary.status_keseluruhan === 'lunas'">✓</span>
        <span v-else-if="summary.status_keseluruhan === 'menunggu'">⏳</span>
        <span v-else>⚠️</span>
      </div>
      <div class="banner-content">
        <div class="banner-header-row">
          <h2 class="banner-title">
            STATUS PEMBAYARAN: {{ bannerTitle }}
          </h2>
          <span class="banner-badge" :class="`badge-${summary.status_keseluruhan}`">
            {{ bannerBadgeText }}
          </span>
        </div>
        <p class="banner-desc">{{ summary.keterangan || defaultKeterangan }}</p>
      </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-icon icon-blue">💰</span>
          <span class="stat-label">Total Tagihan</span>
        </div>
        <div class="stat-val">{{ summary.total_tagihan_formatted || 'Rp 0' }}</div>
        <div class="stat-sub">Semua kewajiban terdaftar</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-icon icon-green">✅</span>
          <span class="stat-label">Sudah Dibayar (Lunas)</span>
        </div>
        <div class="stat-val text-success">{{ summary.total_dibayar_formatted || 'Rp 0' }}</div>
        <div class="stat-sub">Terverifikasi administrasi</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-icon icon-amber">⏳</span>
          <span class="stat-label">Sisa Belum Lunas</span>
        </div>
        <div class="stat-val" :class="summary.sisa_tagihan > 0 ? 'text-danger' : 'text-success'">
          {{ summary.sisa_tagihan_formatted || 'Rp 0' }}
        </div>
        <div class="stat-sub">{{ summary.sisa_tagihan > 0 ? 'Perlu diselesaikan' : 'Kewajiban telah terpenuhi' }}</div>
      </div>
    </div>

    <!-- Daftar Rincian Tagihan -->
    <div class="panel-card">
      <div class="panel-header">
        <h3 class="panel-title">Rincian Daftar Tagihan Siswa</h3>
        <span class="tag-counter">{{ tagihans.length }} Pos Tagihan</span>
      </div>

      <div v-if="loading && !tagihans.length" class="loading-state">
        <div class="spinner"></div>
        <p>Memuat data tagihan & pembayaran...</p>
      </div>

      <div v-else-if="!tagihans.length" class="empty-state">
        <div class="empty-icon">💳</div>
        <p>Belum ada daftar tagihan yang ditugaskan untuk akun Anda.</p>
      </div>

      <div v-else class="tagihan-list">
        <div 
          v-for="t in tagihans" 
          :key="t.id" 
          class="tagihan-item"
          :class="{ 
            'item-lunas': t.status === 'lunas',
            'item-menunggu': t.status === 'menunggu',
            'item-unpaid': t.status === 'belum_bayar' || t.status === 'ditolak'
          }"
        >
          <div class="tagihan-main-info">
            <div class="tagihan-title-row">
              <span class="jenis-badge">{{ t.jenis }}</span>
              <h4 class="tagihan-nama">{{ t.judul }}</h4>
            </div>

            <div class="tagihan-meta-row">
              <div class="meta-col">
                <span class="meta-label">Nominal Tagihan:</span>
                <span class="meta-val font-bold text-dark">{{ t.nominal_formatted }}</span>
              </div>
              <div class="meta-col">
                <span class="meta-label">Jatuh Tempo:</span>
                <span class="meta-val">{{ formatDate(t.jatuh_tempo) }}</span>
              </div>
            </div>

            <!-- Catatan atau Info Pembayaran -->
            <div v-if="t.pembayaran_terakhir" class="pembayaran-info-box">
              <div class="pembayaran-info-header">
                <span class="info-title">Riwayat Pembayaran Terakhir:</span>
                <span class="metode-tag">{{ t.pembayaran_terakhir.metode || 'TRANSFER_BANK' }}</span>
              </div>

              <div class="pembayaran-info-details">
                <span>Dibayar: <strong>Rp {{ formatRupiah(t.pembayaran_terakhir.nominal_bayar) }}</strong></span>
                <span v-if="t.pembayaran_terakhir.verifikator">
                  • Diverifikasi oleh: <strong>{{ t.pembayaran_terakhir.verifikator }}</strong>
                </span>
                <span v-if="t.pembayaran_terakhir.paid_at">
                  • Waktu: {{ formatDateTime(t.pembayaran_terakhir.paid_at) }}
                </span>
              </div>

              <!-- Rejection Note if rejected by staff -->
              <div v-if="t.pembayaran_terakhir.status === 'ditolak' && t.pembayaran_terakhir.catatan" class="catatan-tolak-box">
                <span class="tolak-label">⚠️ Catatan Penolakan dari Administrasi:</span>
                <p class="tolak-text">{{ t.pembayaran_terakhir.catatan }}</p>
              </div>
            </div>
          </div>

          <!-- Status & Actions -->
          <div class="tagihan-action-box">
            <div class="status-indicator">
              <span class="status-chip" :class="getStatusChipClass(t.status)">
                {{ getStatusChipLabel(t.status) }}
              </span>
            </div>

            <div class="action-buttons">
              <!-- If Lunas -->
              <template v-if="t.status === 'lunas'">
                <button 
                  v-if="t.pembayaran_terakhir?.bukti_url" 
                  @click="openBukti(t.pembayaran_terakhir.bukti_url, t.judul)"
                  class="btn-action btn-outline"
                >
                  👁️ Lihat Bukti Transfer
                </button>
                <span v-else class="lunas-mark">✓ Lunas</span>
              </template>

              <!-- If Menunggu -->
              <template v-else-if="t.status === 'menunggu'">
                <button 
                  v-if="t.pembayaran_terakhir?.bukti_url" 
                  @click="openBukti(t.pembayaran_terakhir.bukti_url, t.judul)"
                  class="btn-action btn-outline"
                >
                  👁️ Bukti Terkirim
                </button>
                <div class="menunggu-note">Staff sedang memeriksa</div>
              </template>

              <!-- If Belum Bayar / Ditolak -->
              <template v-else>
                <button 
                  @click="openModalBayar(t)" 
                  class="btn-action btn-pay"
                >
                  💳 Bayar & Upload Bukti
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Upload Bukti Transfer -->
    <Teleport to="body">
      <div v-if="showModalBayar" class="modal-backdrop" @click="closeModalBayar">
        <div class="modal-card" @click.stop>
          <div class="modal-header">
            <div>
              <h3 class="modal-title">Konfirmasi Pembayaran Tagihan</h3>
              <p class="modal-subtitle">{{ activeTagihan?.judul }}</p>
            </div>
            <button class="modal-close-btn" @click="closeModalBayar">✕</button>
          </div>

          <div class="modal-body">
            <!-- Informasi Rekening Sekolah -->
            <div class="bank-info-card">
              <div class="bank-card-title">Rekening Resmi Sekolah (Tujuan Transfer):</div>
              <div class="bank-items">
                <div class="bank-row">
                  <span class="bank-badge">BCA</span>
                  <div class="bank-details">
                    <span class="rek-no">1234 5678 90</span>
                    <span class="rek-owner">a.n. SLA Purwodadi</span>
                  </div>
                </div>
                <div class="bank-row">
                  <span class="bank-badge badge-bni">BNI</span>
                  <div class="bank-details">
                    <span class="rek-no">0987 654 321</span>
                    <span class="rek-owner">a.n. SLA Purwodadi</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Upload -->
            <form @submit.prevent="submitBayar" class="upload-form">
              <div class="form-group">
                <label class="form-label">Nominal Transfer (Rp):</label>
                <input 
                  type="number" 
                  v-model="formBayar.nominal" 
                  class="form-input font-bold" 
                  required 
                />
              </div>

              <div class="form-group">
                <label class="form-label">Metode Pembayaran:</label>
                <select v-model="formBayar.metode" class="form-input">
                  <option value="TRANSFER_BANK">Transfer Bank Manual</option>
                  <option value="ATM">Transfer ATM</option>
                  <option value="M_BANKING">Mobile Banking / Internet Banking</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Unggah Bukti Transfer (JPG, PNG, WebP, PDF):</label>
                <div class="file-drop-box" :class="{ 'has-file': formBayar.file }">
                  <input 
                    type="file" 
                    id="bukti-file-input" 
                    accept="image/*,application/pdf" 
                    @change="handleFileChange" 
                    class="file-hidden-input" 
                    required 
                  />
                  <label for="bukti-file-input" class="file-label">
                    <span class="upload-icon">📁</span>
                    <span v-if="formBayar.file" class="file-chosen-name">
                      {{ formBayar.file.name }}
                    </span>
                    <span v-else class="upload-prompt">
                      Klik untuk memilih file foto/struk transfer
                    </span>
                    <span class="file-format-hint">Maksimal 5MB (Format Foto atau PDF)</span>
                  </label>
                </div>
              </div>

              <p v-if="uploadError" class="error-msg">{{ uploadError }}</p>

              <div class="modal-footer">
                <button type="button" @click="closeModalBayar" class="btn-cancel" :disabled="submitting">
                  Batal
                </button>
                <button type="submit" class="btn-submit" :disabled="submitting || !formBayar.file">
                  <span v-if="submitting" class="spinner-sm"></span>
                  {{ submitting ? 'Mengunggah...' : 'Kirim Bukti Pembayaran' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Lihat Bukti -->
    <Teleport to="body">
      <div v-if="previewBuktiUrl" class="modal-backdrop" @click="previewBuktiUrl = null">
        <div class="modal-card preview-card" @click.stop>
          <div class="modal-header">
            <h3 class="modal-title">Bukti Transfer - {{ previewBuktiTitle }}</h3>
            <button class="modal-close-btn" @click="previewBuktiUrl = null">✕</button>
          </div>
          <div class="modal-body preview-body">
            <iframe 
              v-if="previewBuktiUrl.endsWith('.pdf')" 
              :src="previewBuktiUrl" 
              class="preview-frame"
            ></iframe>
            <img 
              v-else 
              :src="previewBuktiUrl" 
              class="preview-img" 
              alt="Bukti Transfer" 
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { usePendaftaranStore } from '@/stores/pendaftaran';

const authStore = useAuthStore();
const pendaftaranStore = usePendaftaranStore();

const loading = ref(true);
const submitting = ref(false);
const uploadError = ref('');

const summary = ref({
  total_tagihan: 0,
  total_dibayar: 0,
  sisa_tagihan: 0,
  total_tagihan_formatted: 'Rp 0',
  total_dibayar_formatted: 'Rp 0',
  sisa_tagihan_formatted: 'Rp 0',
  status_keseluruhan: 'belum_bayar',
  keterangan: '',
});

const tagihans = ref([]);
const activeTagihan = ref(null);
const showModalBayar = ref(false);
const previewBuktiUrl = ref(null);
const previewBuktiTitle = ref('');

const formBayar = ref({
  nominal: 0,
  metode: 'TRANSFER_BANK',
  file: null,
});

// Banner styling & labels
const bannerClass = computed(() => {
  if (summary.value.status_keseluruhan === 'lunas') return 'banner-lunas';
  if (summary.value.status_keseluruhan === 'menunggu') return 'banner-menunggu';
  return 'banner-unpaid';
});

const bannerTitle = computed(() => {
  if (summary.value.status_keseluruhan === 'lunas') return 'LUNAS';
  if (summary.value.status_keseluruhan === 'menunggu') return 'MENUNGGU VERIFIKASI';
  return 'BELUM LUNAS';
});

const bannerBadgeText = computed(() => {
  if (summary.value.status_keseluruhan === 'lunas') return '✓ Terverifikasi';
  if (summary.value.status_keseluruhan === 'menunggu') return '⏳ Proses Verifikasi';
  return 'Belum Lunas';
});

const defaultKeterangan = computed(() => {
  if (summary.value.status_keseluruhan === 'lunas') {
    return 'Semua tagihan pendidikan Anda telah lunas dan diverifikasi oleh Staff Administrasi.';
  }
  if (summary.value.status_keseluruhan === 'menunggu') {
    return 'Bukti pembayaran Anda telah dikirim dan sedang dalam antrean verifikasi oleh Staff Administrasi.';
  }
  return 'Terdapat tagihan yang belum lunas. Silakan lakukan pembayaran dan unggah bukti transfer.';
});

const formatRupiah = (val) => {
  if (!val) return '0';
  return new Intl.NumberFormat('id-ID').format(val);
};

const formatDate = (val) => {
  if (!val) return '-';
  try {
    return new Date(val).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  } catch {
    return val;
  }
};

const formatDateTime = (val) => {
  if (!val) return '-';
  try {
    return new Date(val).toLocaleString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return val;
  }
};

const getStatusChipClass = (status) => {
  if (status === 'lunas') return 'chip-success';
  if (status === 'menunggu') return 'chip-warning';
  if (status === 'ditolak') return 'chip-danger';
  return 'chip-danger';
};

const getStatusChipLabel = (status) => {
  if (status === 'lunas') return 'Lunas';
  if (status === 'menunggu') return 'Menunggu Verifikasi';
  if (status === 'ditolak') return 'Ditolak (Belum Lunas)';
  return 'Belum Lunas';
};

const fetchTagihan = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/siswa/tagihan', {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    summary.value = res.data.summary || summary.value;
    tagihans.value = res.data.tagihans || [];
    pendaftaranStore.tagihanSummary = res.data.summary;
  } catch (err) {
    console.error('Gagal mengambil data tagihan', err);
  } finally {
    loading.value = false;
  }
};

const openModalBayar = (tagihan) => {
  activeTagihan.value = tagihan;
  formBayar.value.nominal = tagihan.nominal;
  formBayar.value.metode = 'TRANSFER_BANK';
  formBayar.value.file = null;
  uploadError.value = '';
  showModalBayar.value = true;
};

const closeModalBayar = () => {
  showModalBayar.value = false;
  activeTagihan.value = null;
};

const handleFileChange = (e) => {
  if (e.target.files && e.target.files[0]) {
    formBayar.value.file = e.target.files[0];
  }
};

const submitBayar = async () => {
  if (!activeTagihan.value || !formBayar.value.file) {
    uploadError.value = 'Silakan pilih file bukti transfer.';
    return;
  }

  submitting.value = true;
  uploadError.value = '';

  const formData = new FormData();
  formData.append('file', formBayar.value.file);
  formData.append('nominal_bayar', formBayar.value.nominal);
  formData.append('metode', formBayar.value.metode);

  try {
    await axios.post(`/api/siswa/tagihan/${activeTagihan.value.id}/bayar`, formData, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'multipart/form-data',
      }
    });

    closeModalBayar();
    await fetchTagihan();
    await pendaftaranStore.fetchTagihan();
  } catch (err) {
    uploadError.value = err.response?.data?.message || 'Gagal mengunggah bukti pembayaran.';
  } finally {
    submitting.value = false;
  }
};

const openBukti = (url, title) => {
  previewBuktiUrl.value = url;
  previewBuktiTitle.value = title;
};

onMounted(() => {
  fetchTagihan();
});
</script>

<style scoped>
.tagihan-page {
  padding: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  gap: 1rem;
}
.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}
.page-subtitle {
  color: #64748b;
  margin: 0.35rem 0 0 0;
  font-size: 0.95rem;
}
.refresh-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  color: #334155;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.15s ease;
}
.refresh-btn:hover:not(:disabled) {
  border-color: #2563eb;
  color: #2563eb;
  background: #f8fafc;
}
.spin-icon {
  animation: spin 1s linear infinite;
}

/* Status Banner */
.status-banner {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  border: 1.5px solid transparent;
}
.banner-lunas {
  background: #ecfdf5;
  border-color: #10b981;
}
.banner-menunggu {
  background: #fefce8;
  border-color: #f59e0b;
}
.banner-unpaid {
  background: #fef2f2;
  border-color: #ef4444;
}

.banner-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 800;
  flex-shrink: 0;
}
.banner-lunas .banner-icon-box {
  background: #10b981;
  color: #ffffff;
}
.banner-menunggu .banner-icon-box {
  background: #f59e0b;
  color: #ffffff;
}
.banner-unpaid .banner-icon-box {
  background: #ef4444;
  color: #ffffff;
}

.banner-content {
  flex: 1;
}
.banner-header-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.25rem;
}
.banner-title {
  font-size: 1.15rem;
  font-weight: 800;
  margin: 0;
}
.banner-lunas .banner-title { color: #065f46; }
.banner-menunggu .banner-title { color: #854d0e; }
.banner-unpaid .banner-title { color: #991b1b; }

.banner-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}
.badge-lunas { background: #d1fae5; color: #065f46; }
.badge-menunggu { background: #fef3c7; color: #92400e; }
.badge-belum_bayar { background: #fee2e2; color: #991b1b; }

.banner-desc {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.4;
}
.banner-lunas .banner-desc { color: #047857; }
.banner-menunggu .banner-desc { color: #a16207; }
.banner-unpaid .banner-desc { color: #b91c1c; }

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.75rem;
}
.stat-card {
  background: #ffffff;
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}
.stat-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}
.stat-icon {
  font-size: 1.25rem;
}
.stat-label {
  font-size: 0.825rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.stat-val {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
}
.text-success { color: #10b981 !important; }
.text-danger { color: #ef4444 !important; }
.stat-sub {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

/* Panel Card */
.panel-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
  overflow: hidden;
}
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}
.panel-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}
.tag-counter {
  font-size: 0.8rem;
  background: #f1f5f9;
  color: #475569;
  padding: 0.25rem 0.65rem;
  border-radius: 9999px;
  font-weight: 700;
}

/* Tagihan List */
.tagihan-list {
  display: flex;
  flex-direction: column;
}
.tagihan-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  gap: 1.5rem;
  transition: background 0.15s ease;
}
.tagihan-item:last-child {
  border-bottom: none;
}
.tagihan-item:hover {
  background: #fcfdfe;
}
.tagihan-item.item-lunas {
  border-left: 4px solid #10b981;
}
.tagihan-item.item-menunggu {
  border-left: 4px solid #f59e0b;
}
.tagihan-item.item-unpaid {
  border-left: 4px solid #ef4444;
}

.tagihan-main-info {
  flex: 1;
}
.tagihan-title-row {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-bottom: 0.75rem;
}
.jenis-badge {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}
.tagihan-nama {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.tagihan-meta-row {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
}
.meta-col {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.9rem;
}
.meta-label {
  color: #64748b;
}
.meta-val {
  color: #1e293b;
}
.font-bold { font-weight: 700; }
.text-dark { color: #0f172a; }

/* Pembayaran Info Box */
.pembayaran-info-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-top: 0.5rem;
}
.pembayaran-info-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.25rem;
}
.metode-tag {
  background: #e2e8f0;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
}
.pembayaran-info-details {
  font-size: 0.825rem;
  color: #334155;
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.catatan-tolak-box {
  margin-top: 0.5rem;
  background: #fef2f2;
  border-left: 3px solid #ef4444;
  padding: 0.5rem 0.75rem;
  border-radius: 0 6px 6px 0;
}
.tolak-label {
  font-size: 0.775rem;
  font-weight: 700;
  color: #991b1b;
  display: block;
}
.tolak-text {
  font-size: 0.825rem;
  color: #7f1d1d;
  margin: 0.2rem 0 0 0;
}

/* Tagihan Action Box */
.tagihan-action-box {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.75rem;
  min-width: 180px;
}
.status-chip {
  display: inline-flex;
  padding: 0.35rem 0.85rem;
  border-radius: 9999px;
  font-size: 0.775rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.chip-success {
  background: #dcfce7;
  color: #166534;
}
.chip-warning {
  background: #fef9c3;
  color: #854d0e;
}
.chip-danger {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
}
.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.6rem 1.1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
  white-space: nowrap;
}
.btn-pay {
  background: #2563eb;
  color: #ffffff;
}
.btn-pay:hover {
  background: #1d4ed8;
}
.btn-outline {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
}
.btn-outline:hover {
  border-color: #2563eb;
  color: #2563eb;
  background: #f8fafc;
}
.lunas-mark {
  color: #166534;
  font-weight: 700;
  font-size: 0.85rem;
}
.menunggu-note {
  font-size: 0.75rem;
  color: #854d0e;
  font-style: italic;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-card {
  background: #ffffff;
  border-radius: 14px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}
.modal-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}
.modal-subtitle {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0.2rem 0 0 0;
}
.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}
.modal-close-btn:hover {
  color: #0f172a;
}
.modal-body {
  padding: 1.5rem;
}

/* Bank Info Card */
.bank-info-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.25rem;
}
.bank-card-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: #475569;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
}
.bank-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.bank-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
}
.bank-badge {
  background: #0066ae;
  color: #fff;
  font-weight: 800;
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}
.badge-bni {
  background: #f15a24;
}
.bank-details {
  display: flex;
  justify-content: space-between;
  width: 100%;
  font-size: 0.85rem;
}
.rek-no {
  font-weight: 800;
  color: #0f172a;
  letter-spacing: 0.03em;
}
.rek-owner {
  color: #64748b;
}

/* Upload Form */
.form-group {
  margin-bottom: 1.15rem;
}
.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 0.4rem;
}
.form-input {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
  box-sizing: border-box;
}
.form-input:focus {
  border-color: #2563eb;
}

/* File Drop Box */
.file-drop-box {
  border: 2px dashed #cbd5e1;
  border-radius: 8px;
  padding: 1.25rem;
  text-align: center;
  background: #fcfdfe;
  transition: all 0.15s ease;
}
.file-drop-box.has-file {
  border-color: #10b981;
  background: #ecfdf5;
}
.file-hidden-input {
  display: none;
}
.file-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}
.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.25rem;
}
.upload-prompt {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2563eb;
}
.file-chosen-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: #065f46;
}
.file-format-hint {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}
.btn-cancel {
  padding: 0.65rem 1.25rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #fff;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
}
.btn-submit {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.5rem;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}
.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.error-msg {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

/* Preview Modal */
.preview-card {
  max-width: 800px;
}
.preview-body {
  max-height: 80vh;
  overflow: auto;
  display: flex;
  justify-content: center;
  padding: 1rem;
  background: #0f172a;
}
.preview-img {
  max-width: 100%;
  max-height: 75vh;
  object-fit: contain;
}
.preview-frame {
  width: 100%;
  height: 75vh;
  border: none;
}

/* Spinner */
.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.75rem auto;
}
.spinner-sm {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
.loading-state, .empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #64748b;
}
.empty-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .tagihan-item {
    flex-direction: column;
    align-items: stretch;
    gap: 1.25rem;
    padding: 1.25rem 1rem;
  }
  .tagihan-action-box {
    align-items: stretch;
    width: 100%;
    min-width: 0;
  }
  .status-chip {
    text-align: center;
    justify-content: center;
  }
  .btn-bayar-tagihan, .btn-lihat-bukti {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 640px) {
  .tagihan-page {
    padding: 0.25rem 0;
  }
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  .status-banner {
    flex-direction: column;
    align-items: flex-start;
    padding: 1.25rem 1rem;
  }
  .tagihan-meta-row {
    gap: 0.75rem;
  }
}
</style>
