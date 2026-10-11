<template>
  <div class="detail-page">
    <!-- Header -->
    <div class="page-header">
      <router-link to="/administrasi/pembayaran" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Data Pembayaran
      </router-link>
      <div class="header-main">
        <div>
          <h2 class="page-title">Verifikasi Pembayaran Siswa</h2>
          <p class="page-subtitle" v-if="pembayaran">
            Pendaftar: <strong>{{ pembayaran.tagihan?.student?.full_name || 'Tidak Diketahui' }}</strong>
            <span v-if="pembayaran.tagihan?.student?.nomor_pendaftaran" class="reg-no">
              ({{ pembayaran.tagihan.student.nomor_pendaftaran }})
            </span>
          </p>
        </div>
        <div class="header-badge" v-if="pembayaran">
          <span class="status-badge" :class="getStatusBadge(pembayaran.status)">
            {{ formatStatus(pembayaran.status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Student Other Payments Switcher -->
    <div v-if="pembayaran?.other_payments?.length > 1" class="doc-switcher">
      <div class="switcher-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        <span>Pembayaran Lain Siswa Ini:</span>
      </div>
      <div class="switcher-tabs">
        <router-link
          v-for="item in pembayaran.other_payments"
          :key="item.id"
          :to="`/administrasi/pembayaran/${item.id}`"
          class="switcher-tab"
          :class="{ active: String(item.id) === String(route.params.id) }"
        >
          <span class="tab-name">{{ item.tagihan_jenis }}</span>
          <span class="tab-nominal">Rp {{ formatRupiah(item.nominal) }}</span>
          <span class="tab-status" :class="`tab-${item.status}`">
            {{ item.status === 'terverifikasi' ? '✓ Lunas' : item.status === 'ditolak' ? '✗' : '⏳' }}
          </span>
        </router-link>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div v-if="pembayaran" class="content-grid">
      <!-- Preview Panel (Bukti Transfer) -->
      <div class="preview-panel">
        <div class="preview-header">
          <div class="preview-title-box">
            <h3 class="preview-title">
              Bukti Transfer — {{ pembayaran.tagihan?.jenis || 'Pembayaran' }}
            </h3>
            <div class="file-badges">
              <span class="badge-type badge-metode">{{ pembayaran.metode || 'TRANSFER_BANK' }}</span>
              <span class="badge-meta font-bold text-dark">
                Nominal: Rp {{ formatRupiah(pembayaran.nominal_display || pembayaran.tagihan?.nominal) }}
              </span>
              <span v-if="pembayaran.file_name" class="badge-meta filename-truncate" :title="pembayaran.file_name">
                {{ pembayaran.file_name }}
              </span>
            </div>
          </div>

          <!-- Toolbar Actions -->
          <div class="preview-toolbar" v-if="resolvedBuktiUrl">
            <template v-if="!isPdf">
              <button @click="zoomOut" class="tool-btn" title="Perkecil (-)" :disabled="zoomLevel <= 0.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
              </button>
              <button @click="resetZoom" class="tool-btn text-xs" title="Reset Zoom">
                {{ Math.round(zoomLevel * 100) }}%
              </button>
              <button @click="zoomIn" class="tool-btn" title="Perbesar (+)" :disabled="zoomLevel >= 3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
              </button>
              <button @click="rotateImage" class="tool-btn" title="Putar 90°">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
              </button>
            </template>

            <button @click="toggleFullscreen" class="tool-btn" title="Layar Penuh">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            </button>

            <a :href="resolvedBuktiUrl" target="_blank" rel="noopener noreferrer" class="tool-btn" title="Buka Tab Baru">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            </a>

            <a :href="resolvedBuktiUrl" :download="pembayaran.file_name || 'bukti_transfer'" class="tool-btn primary-tool" title="Unduh File">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span class="tool-text">Unduh</span>
            </a>
          </div>
        </div>

        <!-- Viewport Preview Langsung -->
        <div class="file-preview-container" :class="{ 'pdf-mode': isPdf }">
          <div v-if="resolvedBuktiUrl && !isPdf" class="image-viewport">
            <img
              :src="resolvedBuktiUrl"
              class="preview-image"
              :style="{
                transform: `scale(${zoomLevel}) rotate(${rotationDeg}deg)`
              }"
              loading="eager"
              decoding="async"
              alt="Bukti Transfer"
              @error="handleImageError"
            />
          </div>

          <div v-else-if="resolvedBuktiUrl && isPdf" class="pdf-viewport">
            <iframe
              :src="pdfViewerUrl"
              class="preview-pdf-iframe"
              frameborder="0"
              loading="eager"
              title="Bukti Transfer PDF"
            ></iframe>
          </div>

          <div v-else class="unsupported-file">
            <div class="unsupported-icon">🧾</div>
            <h4 class="unsupported-title">Tidak Ada Berkas Bukti Transfer Terlampir</h4>
            <p class="unsupported-desc">
              Pembayaran ini dilakukan melalui sistem otomatis atau murid belum mengunggah struk transfer.
            </p>
          </div>

          <div v-if="imageError" class="preview-error-overlay">
            <div class="error-box">
              <span class="error-icon">⚠️</span>
              <p>Gagal memuat struk bukti transfer dari server.</p>
              <div class="error-actions">
                <button @click="imageError = false" class="btn-sm btn-retry">Coba Lagi</button>
                <a :href="resolvedBuktiUrl" target="_blank" class="btn-sm btn-open">Buka URL Berkas</a>
              </div>
            </div>
          </div>
        </div>

        <div class="preview-footer">
          <span class="footer-hint">
            💡 Periksa keabsahan stempel, nomor rekening pengirim/penerima, dan kecocokan nominal transfer sebelum memverifikasi.
          </span>
        </div>
      </div>

      <!-- Action Panel -->
      <div class="action-panel">
        <!-- Informasi Pembayaran -->
        <div class="info-card">
          <h3 class="panel-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Informasi Pendaftar
          </h3>
          <div class="info-list">
            <div class="info-item">
              <span class="label">Nama Lengkap</span>
              <span class="val font-bold">{{ pembayaran.tagihan?.student?.full_name || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Nomor Pendaftaran</span>
              <span class="val">{{ pembayaran.tagihan?.student?.nomor_pendaftaran || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Jenis Kelamin</span>
              <span class="val">{{ pembayaran.tagihan?.student?.gender === 'laki-laki' || pembayaran.tagihan?.student?.gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
            </div>
          </div>

          <hr class="card-divider" />

          <h3 class="panel-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            Detail Tagihan & Transaksi
          </h3>
          <div class="info-list">
            <div class="info-item">
              <span class="label">Jenis Tagihan</span>
              <span class="val font-semibold text-primary uppercase">{{ pembayaran.tagihan?.jenis || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Nominal Ditagihkan</span>
              <span class="val">Rp {{ formatRupiah(pembayaran.tagihan?.nominal) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Nominal yang Ditransfer</span>
              <span class="val font-bold text-success" style="font-size: 1.15rem;">
                Rp {{ formatRupiah(pembayaran.nominal_display || pembayaran.tagihan?.nominal) }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Tanggal Pengajuan</span>
              <span class="val">{{ formatDate(pembayaran.created_at) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Status Saat Ini</span>
              <span class="status-badge" :class="getStatusBadge(pembayaran.status)">
                {{ formatStatus(pembayaran.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Verification Action Card -->
        <div class="verifikasi-card" v-if="pembayaran.status === 'menunggu' || isEditingStatus">
          <div class="verifikasi-card-header">
            <h3 class="panel-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              Tindakan Verifikasi Pembayaran
            </h3>
            <button v-if="isEditingStatus" @click="isEditingStatus = false" class="btn-cancel-edit">
              Batal
            </button>
          </div>

          <div class="form-group">
            <label class="form-label">Tentukan Keputusan:</label>
            <div class="radio-options">
              <label class="radio-card valid-card" :class="{ selected: form.status === 'terverifikasi' }">
                <input type="radio" v-model="form.status" value="terverifikasi" />
                <div class="radio-content">
                  <div class="radio-title">✓ Disetujui (Lunas)</div>
                  <div class="radio-desc">Dana telah masuk ke rekening dan bukti transfer sah.</div>
                </div>
              </label>

              <label class="radio-card invalid-card" :class="{ selected: form.status === 'ditolak' }">
                <input type="radio" v-model="form.status" value="ditolak" />
                <div class="radio-content">
                  <div class="radio-title">✗ Ditolak (Belum Lunas / Tidak Valid)</div>
                  <div class="radio-desc">Bukti buram, dana belum masuk, atau nominal tidak sesuai.</div>
                </div>
              </label>
            </div>
          </div>

          <div class="form-group" v-if="form.status === 'ditolak'">
            <label class="form-label">
              Catatan Penolakan <span class="required">*</span>:
            </label>
            <textarea
              v-model="form.catatan"
              class="textarea-input"
              rows="3"
              placeholder="Contoh: Nominal transfer kurang Rp 500.000, harap transfer kekurangannya dan kirim bukti baru."
              required
            ></textarea>
            <span class="help-text">Catatan ini akan langsung tampil di menu Tagihan siswa.</span>
          </div>

          <button
            @click="submitVerifikasi"
            class="submit-btn"
            :disabled="submitting || (form.status === 'ditolak' && !form.catatan.trim())"
          >
            <span v-if="submitting" class="spinner"></span>
            {{ submitting ? 'Menyimpan...' : 'Simpan Keputusan Verifikasi' }}
          </button>

          <p v-if="errorMsg" class="error-text">{{ errorMsg }}</p>
        </div>

        <!-- Riwayat Verifikasi -->
        <div class="verifikasi-card verified-summary" v-else>
          <div class="verified-header">
            <h3 class="panel-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
              Riwayat Verifikasi
            </h3>
            <button @click="openEditStatus" class="btn-change-status">
              Ubah Keputusan
            </button>
          </div>

          <div class="info-list">
            <div class="info-item">
              <span class="label">Keterangan Status</span>
              <span class="val font-bold" :class="pembayaran.status === 'terverifikasi' ? 'text-success' : 'text-danger'">
                {{ pembayaran.status === 'terverifikasi' ? 'SUDAH LUNAS' : 'BELUM LUNAS (Ditolak)' }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Diverifikasi Oleh</span>
              <span class="val font-semibold">{{ pembayaran.verifikator?.name || 'Staff Administrasi' }}</span>
            </div>
            <div class="info-item" v-if="pembayaran.paid_at">
              <span class="label">Waktu Pelunasan</span>
              <span class="val">{{ formatDate(pembayaran.paid_at) }}</span>
            </div>
            <div class="info-item" v-if="pembayaran.catatan">
              <span class="label">Catatan Administrasi</span>
              <div class="catatan-bubble" :class="{ 'catatan-rejected': pembayaran.status === 'ditolak' }">
                {{ pembayaran.catatan }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fullscreen Modal -->
    <Teleport to="body">
      <div v-if="isFullscreen" class="fullscreen-modal" @keydown.esc="toggleFullscreen" tabindex="0">
        <div class="modal-backdrop" @click="toggleFullscreen"></div>
        <div class="modal-dialog">
          <div class="modal-header">
            <div class="modal-info">
              <h4 class="modal-title">Bukti Transfer - {{ pembayaran?.tagihan?.student?.full_name }}</h4>
              <span class="modal-meta">Rp {{ formatRupiah(pembayaran?.nominal_display) }}</span>
            </div>
            <div class="modal-actions">
              <button @click="zoomOut" class="tool-btn" title="Perkecil">-</button>
              <button @click="resetZoom" class="tool-btn text-xs">{{ Math.round(zoomLevel * 100) }}%</button>
              <button @click="zoomIn" class="tool-btn" title="Perbesar">+</button>
              <button @click="rotateImage" class="tool-btn" title="Putar">↻</button>
              <button @click="toggleFullscreen" class="btn-close-modal" title="Tutup">✕</button>
            </div>
          </div>
          <div class="modal-body">
            <img
              :src="resolvedBuktiUrl"
              class="modal-image"
              :style="{ transform: `scale(${zoomLevel}) rotate(${rotationDeg}deg)` }"
              alt="Bukti Transfer Fullscreen"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const pembayaran = ref(null);
const submitting = ref(false);
const errorMsg = ref('');
const isEditingStatus = ref(false);

const zoomLevel = ref(1);
const rotationDeg = ref(0);
const isFullscreen = ref(false);
const imageError = ref(false);

const form = ref({
  status: 'terverifikasi',
  catatan: ''
});

// Resolve relative /storage url
const resolvedBuktiUrl = computed(() => {
  if (!pembayaran.value) return '';
  if (pembayaran.value.bukti_transfer_relative_url) {
    return pembayaran.value.bukti_transfer_relative_url;
  }
  if (pembayaran.value.bukti_transfer_url) {
    const match = pembayaran.value.bukti_transfer_url.match(/^https?:\/\/[^/]+(\/storage\/.+)$/);
    if (match) return match[1];
    return pembayaran.value.bukti_transfer_url;
  }
  if (pembayaran.value.bukti_transfer_path) {
    return '/storage/' + pembayaran.value.bukti_transfer_path.replace(/^\/+/, '');
  }
  return '';
});

const isPdf = computed(() => {
  const p = (pembayaran.value?.bukti_transfer_path || pembayaran.value?.file_name || '').toLowerCase();
  return /\.pdf$/i.test(p);
});

const pdfViewerUrl = computed(() => {
  if (!resolvedBuktiUrl.value) return '';
  return `${resolvedBuktiUrl.value}#toolbar=1&navpanes=0&view=FitH`;
});

const zoomIn = () => {
  if (zoomLevel.value < 3) zoomLevel.value = +(zoomLevel.value + 0.25).toFixed(2);
};
const zoomOut = () => {
  if (zoomLevel.value > 0.5) zoomLevel.value = +(zoomLevel.value - 0.25).toFixed(2);
};
const resetZoom = () => {
  zoomLevel.value = 1;
};
const rotateImage = () => {
  rotationDeg.value = (rotationDeg.value + 90) % 360;
};
const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
};
const handleImageError = () => {
  imageError.value = true;
};

const formatRupiah = (val) => {
  if (!val) return '0';
  return new Intl.NumberFormat('id-ID').format(val);
};

const getStatusBadge = (status) => {
  if (status === 'terverifikasi') return 'badge-success';
  if (status === 'ditolak') return 'badge-danger';
  return 'badge-warning';
};

const formatStatus = (status) => {
  if (status === 'terverifikasi') return 'Lunas';
  if (status === 'ditolak') return 'Ditolak (Belum Lunas)';
  return 'Menunggu Verifikasi';
};

const formatDate = (val) => {
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

const openEditStatus = () => {
  form.value.status = pembayaran.value?.status === 'ditolak' ? 'ditolak' : 'terverifikasi';
  form.value.catatan = pembayaran.value?.catatan || '';
  isEditingStatus.value = true;
};

const fetchDetail = async (id) => {
  const pId = id || route.params.id;
  imageError.value = false;
  zoomLevel.value = 1;
  rotationDeg.value = 0;
  isEditingStatus.value = false;

  try {
    const res = await axios.get(`/api/v1/staff/pembayaran/${pId}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    pembayaran.value = res.data;
    form.value.status = res.data.status === 'ditolak' ? 'ditolak' : 'terverifikasi';
    form.value.catatan = res.data.catatan || '';
  } catch (err) {
    console.error('Gagal mengambil detail pembayaran', err);
    errorMsg.value = 'Gagal memuat detail pembayaran.';
  }
};

const submitVerifikasi = async () => {
  if (form.value.status === 'ditolak' && !form.value.catatan.trim()) {
    errorMsg.value = 'Catatan penolakan harus diisi agar pendaftar mengetahui tindak lanjutnya.';
    return;
  }

  submitting.value = true;
  errorMsg.value = '';

  try {
    await axios.post(`/api/v1/staff/pembayaran/${route.params.id}/verifikasi`, {
      status: form.value.status,
      catatan: form.value.status === 'ditolak' ? form.value.catatan : ''
    }, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });

    await fetchDetail();
    isEditingStatus.value = false;
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Gagal menyimpan keputusan verifikasi pembayaran.';
  } finally {
    submitting.value = false;
  }
};

watch(() => route.params.id, (newId) => {
  if (newId) fetchDetail(newId);
});

onMounted(() => {
  fetchDetail();
});
</script>

<style scoped>
.detail-page {
  padding: 1.5rem;
  max-width: 1560px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  margin-bottom: 1.25rem;
}
.back-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-bottom: 0.5rem;
  transition: color 0.15s;
}
.back-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}
.header-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}
.page-title {
  font-size: 1.65rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.02em;
}
.page-subtitle {
  margin: 0.25rem 0 0 0;
  color: #475569;
  font-size: 0.95rem;
}
.reg-no {
  color: #64748b;
  font-weight: 500;
  margin-left: 0.35rem;
}

/* Doc Switcher */
.doc-switcher {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #f1f5f9;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  overflow-x: auto;
}
.switcher-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.825rem;
  font-weight: 700;
  color: #475569;
  white-space: nowrap;
}
.switcher-tabs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.switcher-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #ffffff;
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  font-size: 0.825rem;
  font-weight: 600;
  color: #334155;
  text-decoration: none;
  border: 1px solid #cbd5e1;
  transition: all 0.15s ease;
  white-space: nowrap;
}
.switcher-tab:hover {
  border-color: #3b82f6;
  color: #2563eb;
  background: #f8fafc;
}
.switcher-tab.active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}
.tab-nominal {
  font-weight: 700;
}
.tab-status {
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 800;
}
.switcher-tab.active .tab-status {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}
.switcher-tab:not(.active) .tab-terverifikasi {
  background: #dcfce7;
  color: #166534;
}
.switcher-tab:not(.active) .tab-ditolak {
  background: #fee2e2;
  color: #991b1b;
}
.switcher-tab:not(.active) .tab-menunggu {
  background: #fef9c3;
  color: #854d0e;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}
@media (min-width: 1100px) {
  .content-grid {
    grid-template-columns: 1.65fr 1fr;
    align-items: start;
  }
}

/* Preview Panel */
.preview-panel {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 1.25rem;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
  display: flex;
  flex-direction: column;
}
.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}
.preview-title-box {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.preview-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.file-badges {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}
.badge-type {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  text-transform: uppercase;
}
.badge-metode { background: #e0f2fe; color: #0284c7; }
.badge-meta {
  font-size: 0.75rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
}
.filename-truncate {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Toolbar */
.preview-toolbar {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: wrap;
}
.tool-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  height: 34px;
  min-width: 34px;
  padding: 0 0.5rem;
  background: #f8fafc;
  color: #334155;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s ease;
}
.tool-btn:hover:not(:disabled) {
  background: #e2e8f0;
  color: #0f172a;
  border-color: #94a3b8;
}
.tool-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.primary-tool {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}
.primary-tool:hover {
  background: #1d4ed8;
  color: #ffffff;
}
.tool-text { font-size: 0.8rem; }

/* Viewport */
.file-preview-container {
  position: relative;
  width: 100%;
  height: 640px;
  background: #0f172a;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #334155;
}
.file-preview-container.pdf-mode {
  background: #ffffff;
  border-color: #cbd5e1;
}

.image-viewport {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: auto;
  padding: 1rem;
}
.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  border-radius: 4px;
  user-select: none;
  transition: transform 0.15s ease-out;
}
.pdf-viewport {
  width: 100%;
  height: 100%;
}
.preview-pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
  background: #f8fafc;
}

.unsupported-file {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #e2e8f0;
}
.unsupported-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}
.unsupported-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}
.unsupported-desc {
  font-size: 0.875rem;
  color: #94a3b8;
  max-width: 360px;
  margin: 0 auto;
}

.preview-error-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  z-index: 6;
}
.error-box {
  background: #1e293b;
  border: 1px solid #ef4444;
  border-radius: 10px;
  padding: 1.5rem;
  text-align: center;
  color: #f1f5f9;
  max-width: 360px;
}
.error-icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
.error-actions { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; }
.btn-sm {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
}
.btn-retry { background: #3b82f6; color: #fff; border: none; }
.btn-open { background: #334155; color: #f1f5f9; }

.preview-footer {
  margin-top: 0.75rem;
}
.footer-hint {
  font-size: 0.775rem;
  color: #64748b;
  font-style: italic;
}

/* Action Panel */
.action-panel {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.info-card, .verifikasi-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 1.25rem;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
}
.panel-card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 0.45rem;
}
.card-divider {
  border: none;
  border-top: 1px solid #f1f5f9;
  margin: 1.25rem 0;
}
.info-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}
.info-item .label {
  font-size: 0.775rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 600;
}
.info-item .val {
  font-size: 0.95rem;
  color: #0f172a;
}
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }
.text-primary { color: #2563eb !important; }
.text-success { color: #10b981 !important; }
.text-danger { color: #ef4444 !important; }

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.775rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  width: fit-content;
}
.badge-success { background: #dcfce7; color: #166534; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-warning { background: #fef9c3; color: #854d0e; }

/* Verifikasi Form */
.verifikasi-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.verifikasi-card-header .panel-card-title {
  margin: 0;
}
.btn-cancel-edit {
  background: none;
  border: 1px solid #cbd5e1;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  color: #64748b;
  cursor: pointer;
}
.form-group {
  margin-bottom: 1rem;
}
.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.4rem;
}
.required { color: #ef4444; }
.radio-options {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}
.radio-card {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  padding: 0.75rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.radio-card input { margin-top: 0.2rem; }
.radio-content { display: flex; flex-direction: column; gap: 0.15rem; }
.radio-title { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
.radio-desc { font-size: 0.75rem; color: #64748b; }
.valid-card.selected { border-color: #10b981; background: #ecfdf5; }
.valid-card.selected .radio-title { color: #065f46; }
.invalid-card.selected { border-color: #ef4444; background: #fef2f2; }
.invalid-card.selected .radio-title { color: #991b1b; }

.textarea-input {
  width: 100%;
  padding: 0.65rem 0.75rem;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.85rem;
  resize: vertical;
  min-height: 85px;
  outline: none;
  box-sizing: border-box;
}
.textarea-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}
.help-text {
  display: block;
  font-size: 0.725rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.submit-btn {
  width: 100%;
  background: #0f172a;
  color: #ffffff;
  border: none;
  padding: 0.85rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.925rem;
  cursor: pointer;
  transition: background 0.15s;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}
.submit-btn:hover:not(:disabled) { background: #1e293b; }
.submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.verified-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.verified-header .panel-card-title { margin: 0; }
.btn-change-status {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.775rem;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
}
.catatan-bubble {
  background: #f8fafc;
  border-left: 3px solid #2563eb;
  padding: 0.6rem 0.75rem;
  border-radius: 0 6px 6px 0;
  font-size: 0.85rem;
  color: #334155;
  line-height: 1.4;
}
.catatan-bubble.catatan-rejected {
  background: #fef2f2;
  border-left-color: #ef4444;
  color: #991b1b;
}
.error-text {
  color: #ef4444;
  font-size: 0.825rem;
  margin-top: 0.5rem;
  text-align: center;
}

/* Fullscreen Modal */
.fullscreen-modal {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  outline: none;
}
.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(4px);
}
.modal-dialog {
  position: relative;
  z-index: 10;
  width: 95vw;
  height: 94vh;
  background: #0f172a;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.25rem;
  background: #1e293b;
  border-bottom: 1px solid #334155;
}
.modal-title { color: #fff; font-size: 1rem; font-weight: 700; margin: 0; }
.modal-meta { color: #94a3b8; font-size: 0.775rem; }
.modal-actions { display: flex; align-items: center; gap: 0.4rem; }
.btn-close-modal {
  background: #ef4444;
  color: #fff;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-body {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: auto;
  padding: 1rem;
}
.modal-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.2s ease-out;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .doc-detail-page {
    padding: 0.25rem 0;
  }
  .preview-panel {
    padding: 0.75rem;
  }
  .doc-switcher {
    overflow-x: auto;
    flex-wrap: nowrap;
    padding-bottom: 0.5rem;
  }
  .switcher-tab {
    flex-shrink: 0;
  }
  .preview-toolbar {
    width: 100%;
    justify-content: space-between;
  }
  .verifikasi-card {
    padding: 1rem;
  }
}
</style>
