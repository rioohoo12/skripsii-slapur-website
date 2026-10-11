<template>
  <div class="detail-page">
    <!-- Header -->
    <div class="page-header">
      <router-link to="/administrasi/dokumen" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Antrean Dokumen
      </router-link>
      <div class="header-main">
        <div>
          <h2 class="page-title">Verifikasi Dokumen Pendaftar</h2>
          <p class="page-subtitle">
            Pendaftar: <strong>{{ dokumen?.student?.full_name || studentInfo.name || 'Pendaftar' }}</strong>
            <span v-if="dokumen?.student?.nomor_pendaftaran || studentInfo.regNo" class="reg-no">
              ({{ dokumen?.student?.nomor_pendaftaran || studentInfo.regNo }})
            </span>
          </p>
        </div>
        <div class="header-badge" v-if="dokumen?.status">
          <span class="status-badge" :class="getStatusBadge(dokumen.status)">
            {{ formatStatus(dokumen.status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Student Other Documents Switcher (Instant Navigation) -->
    <div v-if="switcherDocs.length" class="doc-switcher">
      <div class="switcher-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Dokumen Siswa:</span>
      </div>
      <div class="switcher-tabs">
        <button
          v-for="item in switcherDocs"
          :key="item.id"
          type="button"
          class="switcher-tab"
          :class="{ active: String(item.id) === String(currentDocId) }"
          @click="selectDocument(item.id)"
        >
          <span class="tab-icon">
            <template v-if="item.file_name?.match(/\.pdf$/i)">📄</template>
            <template v-else>🖼️</template>
          </span>
          <span class="tab-name">{{ item.jenis_nama }}</span>
          <span class="tab-status" :class="`tab-${item.status}`">
            {{ item.status === 'disetujui' ? '✓' : item.status === 'ditolak' ? '✗' : '⏳' }}
          </span>
        </button>
      </div>
    </div>

    <!-- Content Grid (Always rendered directly without blocking loading screens) -->
    <div class="content-grid">
      <!-- Dokumen Preview Panel -->
      <div class="preview-panel">
        <div class="preview-header">
          <div class="preview-title-box">
            <h3 class="preview-title">
              {{ dokumen?.jenis_dokumen?.nama || currentDocType || 'Preview Dokumen' }}
            </h3>
            <div class="file-badges">
              <span v-if="isPdf" class="badge-type badge-pdf">PDF</span>
              <span v-else-if="isImage" class="badge-type badge-image">GAMBAR</span>
              <span v-if="dokumen?.file_size_formatted" class="badge-meta">{{ dokumen.file_size_formatted }}</span>
              <span v-if="dokumen?.file_name" class="badge-meta filename-truncate" :title="dokumen.file_name">
                {{ dokumen.file_name }}
              </span>
            </div>
          </div>

          <!-- Toolbar Actions -->
          <div class="preview-toolbar">
            <!-- Controls for Images -->
            <template v-if="isImage">
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

            <!-- Fullscreen -->
            <button @click="toggleFullscreen" class="tool-btn" title="Layar Penuh" :disabled="!resolvedFileUrl">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
            </button>

            <!-- Open in New Tab -->
            <a v-if="resolvedFileUrl" :href="resolvedFileUrl" target="_blank" rel="noopener noreferrer" class="tool-btn" title="Buka di Tab Baru">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            </a>

            <!-- Download -->
            <a v-if="resolvedFileUrl" :href="resolvedFileUrl" :download="dokumen?.file_name || 'dokumen'" class="tool-btn primary-tool" title="Unduh File">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              <span class="tool-text">Unduh</span>
            </a>
          </div>
        </div>

        <!-- Preview Viewport (Direct Rendering - NO artificial loading overlays) -->
        <div class="file-preview-container" :class="{ 'pdf-mode': isPdf }">
          <!-- State 1: Image Preview (Direct Render) -->
          <div
            v-if="isImage && resolvedFileUrl"
            class="image-viewport"
            :class="{ 'cursor-grab': zoomLevel > 1 }"
          >
            <img
              :src="resolvedFileUrl"
              class="preview-image"
              :style="{
                transform: `scale(${zoomLevel}) rotate(${rotationDeg}deg)`
              }"
              loading="eager"
              decoding="async"
              alt="Preview Dokumen"
              @error="handleImageError"
            />
          </div>

          <!-- State 2: PDF Preview (Direct Render via iframe) -->
          <div v-else-if="isPdf && resolvedFileUrl" class="pdf-viewport">
            <iframe
              :src="pdfViewerUrl"
              class="preview-pdf-iframe"
              frameborder="0"
              loading="eager"
              title="Preview Dokumen PDF"
            ></iframe>
          </div>

          <!-- State 3: Unsupported or File not ready -->
          <div v-else-if="resolvedFileUrl" class="unsupported-file">
            <div class="unsupported-icon">📁</div>
            <h4 class="unsupported-title">Format File Khusus</h4>
            <p class="unsupported-desc">
              File {{ dokumen?.file_name || 'dokumen' }} siap untuk diperiksa langsung.
            </p>
            <a :href="resolvedFileUrl" target="_blank" class="download-link-btn">
              Buka File di Tab Baru
            </a>
          </div>

          <!-- Error Overlay if Image fails -->
          <div v-if="imageError" class="preview-error-overlay">
            <div class="error-box">
              <span class="error-icon">⚠️</span>
              <p>Gagal memuat preview gambar dari server.</p>
              <div class="error-actions">
                <button @click="retryLoadImage" class="btn-sm btn-retry">Coba Lagi</button>
                <a :href="resolvedFileUrl" target="_blank" class="btn-sm btn-open">Buka URL File</a>
              </div>
            </div>
          </div>
        </div>

        <div class="preview-footer">
          <div class="footer-hint">
            <span v-if="isImage">💡 Dokumen gambar tampil langsung. Gunakan tombol zoom atau putar jika diperlukan.</span>
            <span v-else-if="isPdf">💡 Dokumen PDF tampil langsung lengkap dengan navigasi lembar halaman.</span>
            <span v-else>💡 Preview dokumen pendaftar.</span>
          </div>
        </div>
      </div>

      <!-- Action & Info Panel -->
      <div class="action-panel">
        <!-- Informasi Dokumen & Pendaftar -->
        <div class="info-card">
          <h3 class="panel-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Informasi Pendaftar
          </h3>
          <div class="info-list">
            <div class="info-item">
              <span class="label">Nama Lengkap</span>
              <span class="val font-bold">{{ dokumen?.student?.full_name || studentInfo.name || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Nomor Pendaftaran</span>
              <span class="val">{{ dokumen?.student?.nomor_pendaftaran || studentInfo.regNo || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Jenis Kelamin</span>
              <span class="val">{{ formatGender(dokumen?.student?.gender) }}</span>
            </div>
            <div class="info-item" v-if="dokumen?.student?.previous_school_name">
              <span class="label">Asal Sekolah</span>
              <span class="val">{{ dokumen?.student?.previous_school_name }}</span>
            </div>
          </div>

          <hr class="card-divider" />

          <h3 class="panel-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
            Detail Dokumen
          </h3>
          <div class="info-list">
            <div class="info-item">
              <span class="label">Jenis Dokumen</span>
              <span class="val font-semibold text-primary">{{ dokumen?.jenis_dokumen?.nama || currentDocType || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Format Berkas</span>
              <span class="val">{{ dokumen?.jenis_dokumen?.format || 'PDF/JPG/PNG' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Tanggal Unggah</span>
              <span class="val">{{ formatDate(dokumen?.created_at) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Status Saat Ini</span>
              <span class="status-badge" :class="getStatusBadge(dokumen?.status)">
                {{ formatStatus(dokumen?.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Verification Action Card -->
        <div class="verifikasi-card" v-if="dokumen?.status === 'menunggu' || isEditingStatus || !dokumen?.status">
          <div class="verifikasi-card-header">
            <h3 class="panel-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              Tindakan Verifikasi
            </h3>
            <button v-if="isEditingStatus" @click="isEditingStatus = false" class="btn-cancel-edit">
              Batal
            </button>
          </div>
          
          <div class="form-group">
            <label class="form-label">Tentukan Keputusan:</label>
            <div class="radio-options">
              <label class="radio-card valid-card" :class="{ selected: form.status === 'disetujui' }">
                <input type="radio" v-model="form.status" value="disetujui" />
                <div class="radio-content">
                  <div class="radio-title">✓ Disetujui (Valid)</div>
                  <div class="radio-desc">Dokumen jelas, asli, dan memenuhi syarat.</div>
                </div>
              </label>

              <label class="radio-card invalid-card" :class="{ selected: form.status === 'ditolak' }">
                <input type="radio" v-model="form.status" value="ditolak" />
                <div class="radio-content">
                  <div class="radio-title">✗ Ditolak (Tidak Valid)</div>
                  <div class="radio-desc">Dokumen buram, salah upload, atau tidak lengkap.</div>
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
              placeholder="Contoh: Dokumen tidak terbaca dengan jelas (blur). Harap scan ulang dan unggah kembali."
              required
            ></textarea>
            <span class="help-text">Catatan ini akan diteruskan kepada pendaftar.</span>
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

        <!-- Riwayat Verifikasi (Jika sudah diverifikasi) -->
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
              <span class="label">Diverifikasi Oleh</span>
              <span class="val font-semibold">{{ dokumen?.verifikator?.name || 'Staff Administrasi' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Waktu Verifikasi</span>
              <span class="val">{{ formatDate(dokumen?.diverifikasi_at) }}</span>
            </div>
            <div class="info-item" v-if="dokumen?.catatan">
              <span class="label">Catatan</span>
              <div class="catatan-bubble" :class="{ 'catatan-rejected': dokumen?.status === 'ditolak' }">
                {{ dokumen.catatan }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fullscreen Modal View -->
    <Teleport to="body">
      <div v-if="isFullscreen" class="fullscreen-modal" @keydown.esc="toggleFullscreen" tabindex="0">
        <div class="modal-backdrop" @click="toggleFullscreen"></div>
        <div class="modal-dialog">
          <div class="modal-header">
            <div class="modal-info">
              <h4 class="modal-title">{{ dokumen?.jenis_dokumen?.nama }} - {{ dokumen?.student?.full_name }}</h4>
              <span class="modal-meta">{{ dokumen?.file_name }} ({{ dokumen?.file_size_formatted }})</span>
            </div>
            <div class="modal-actions">
              <template v-if="isImage">
                <button @click="zoomOut" class="tool-btn" title="Perkecil">-</button>
                <button @click="resetZoom" class="tool-btn text-xs">{{ Math.round(zoomLevel * 100) }}%</button>
                <button @click="zoomIn" class="tool-btn" title="Perbesar">+</button>
                <button @click="rotateImage" class="tool-btn" title="Putar">↻</button>
              </template>
              <button @click="toggleFullscreen" class="btn-close-modal" title="Tutup (Esc)">✕</button>
            </div>
          </div>
          <div class="modal-body">
            <img
              v-if="isImage"
              :src="resolvedFileUrl"
              class="modal-image"
              :style="{ transform: `scale(${zoomLevel}) rotate(${rotationDeg}deg)` }"
              alt="Preview Fullscreen"
            />
            <iframe
              v-else-if="isPdf"
              :src="pdfViewerUrl"
              class="modal-pdf-iframe"
              frameborder="0"
            ></iframe>
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

// In-memory cache for 0ms instantaneous document switching
const docCache = new Map();

const submitting = ref(false);
const dokumen = ref(null);
const errorMsg = ref('');
const isEditingStatus = ref(false);
const currentDocId = ref(route.params.id);

// Interactive view states (NO artificial fileLoading)
const imageError = ref(false);
const zoomLevel = ref(1);
const rotationDeg = ref(0);
const isFullscreen = ref(false);

const form = ref({
  status: 'disetujui',
  catatan: ''
});

// Student and list memo for instant rendering
const studentInfo = ref({
  name: '',
  regNo: '',
});
const switcherDocs = ref([]);

// Fast doc type title
const currentDocType = computed(() => {
  const match = switcherDocs.value.find(d => String(d.id) === String(currentDocId.value));
  return match?.jenis_nama || '';
});

// Resolve File URL directly via relative /storage path (0ms resolution)
const resolvedFileUrl = computed(() => {
  if (!dokumen.value) return '';
  
  if (dokumen.value.file_relative_url) {
    return dokumen.value.file_relative_url;
  }
  
  if (dokumen.value.file_url) {
    const match = dokumen.value.file_url.match(/^https?:\/\/[^/]+(\/storage\/.+)$/);
    if (match) return match[1];
    return dokumen.value.file_url;
  }
  
  if (dokumen.value.file_path) {
    return '/storage/' + dokumen.value.file_path.replace(/^\/+/, '');
  }
  
  return '';
});

// PDF viewer direct URL with toolbar & fit width
const pdfViewerUrl = computed(() => {
  if (!resolvedFileUrl.value) return '';
  return `${resolvedFileUrl.value}#toolbar=1&navpanes=0&view=FitH`;
});

// Detect File Type
const isImage = computed(() => {
  if (dokumen.value?.file_mime?.startsWith('image/')) return true;
  const p = (dokumen.value?.file_path || dokumen.value?.file_name || '').toLowerCase();
  return /\.(jpeg|jpg|png|webp|gif|svg)$/i.test(p);
});

const isPdf = computed(() => {
  if (dokumen.value?.file_mime === 'application/pdf') return true;
  const p = (dokumen.value?.file_path || dokumen.value?.file_name || '').toLowerCase();
  return /\.pdf$/i.test(p);
});

// Zoom & Rotate helpers
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

const retryLoadImage = () => {
  imageError.value = false;
};

const getStatusBadge = (status) => {
  if (status === 'disetujui') return 'badge-success';
  if (status === 'ditolak') return 'badge-danger';
  return 'badge-warning';
};

const formatStatus = (status) => {
  if (status === 'disetujui') return 'Valid (Disetujui)';
  if (status === 'ditolak') return 'Tidak Valid (Ditolak)';
  return 'Menunggu Verifikasi';
};

const formatGender = (g) => {
  if (!g) return '-';
  if (g === 'laki-laki' || g === 'L') return 'Laki-Laki';
  if (g === 'perempuan' || g === 'P') return 'Perempuan';
  return g;
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
  form.value.status = dokumen.value?.status === 'ditolak' ? 'ditolak' : 'disetujui';
  form.value.catatan = dokumen.value?.catatan || '';
  isEditingStatus.value = true;
};

// Switch document instantly in 0ms if in cache, update route silently
const selectDocument = (id) => {
  currentDocId.value = id;
  router.push(`/administrasi/dokumen/${id}`);
};

const fetchDokumen = async (id) => {
  const docId = id || currentDocId.value || route.params.id;
  currentDocId.value = docId;
  imageError.value = false;
  zoomLevel.value = 1;
  rotationDeg.value = 0;
  isEditingStatus.value = false;

  // 1. Instant cache hit check (0ms display)
  if (docCache.has(String(docId))) {
    const cached = docCache.get(String(docId));
    dokumen.value = cached;
    if (cached.other_documents?.length) {
      switcherDocs.value = cached.other_documents;
    }
    if (cached.student) {
      studentInfo.value.name = cached.student.full_name;
      studentInfo.value.regNo = cached.student.nomor_pendaftaran;
    }
    form.value.status = cached.status === 'ditolak' ? 'ditolak' : 'disetujui';
    form.value.catatan = cached.catatan || '';
  }

  // 2. Fetch fresh data without blocking UI
  try {
    const res = await axios.get(`/api/v1/staff/dokumen/${docId}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    
    dokumen.value = res.data;
    docCache.set(String(docId), res.data);

    if (res.data.other_documents?.length) {
      switcherDocs.value = res.data.other_documents;
    }
    if (res.data.student) {
      studentInfo.value.name = res.data.student.full_name;
      studentInfo.value.regNo = res.data.student.nomor_pendaftaran;
    }

    form.value.status = res.data.status === 'ditolak' ? 'ditolak' : 'disetujui';
    form.value.catatan = res.data.catatan || '';
  } catch (err) {
    console.error('Gagal mengambil detail dokumen', err);
    errorMsg.value = 'Gagal memuat dokumen. Silakan periksa koneksi Anda.';
  }
};

const submitVerifikasi = async () => {
  if (form.value.status === 'ditolak' && !form.value.catatan.trim()) {
    errorMsg.value = 'Catatan penolakan harus diisi agar pendaftar mengetahui perbaikan yang diperlukan.';
    return;
  }
  
  submitting.value = true;
  errorMsg.value = '';
  
  try {
    await axios.post(`/api/v1/staff/dokumen/${currentDocId.value}/verifikasi`, {
      status: form.value.status,
      catatan: form.value.status === 'ditolak' ? form.value.catatan : ''
    }, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    });
    
    // Clear cache and refresh
    docCache.delete(String(currentDocId.value));
    await fetchDokumen(currentDocId.value);
    isEditingStatus.value = false;
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Gagal menyimpan keputusan verifikasi.';
  } finally {
    submitting.value = false;
  }
};

watch(() => route.params.id, (newId) => {
  if (newId && String(newId) !== String(currentDocId.value)) {
    fetchDokumen(newId);
  }
});

onMounted(() => {
  fetchDokumen();
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

/* Document Switcher */
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
  gap: 0.4rem;
  background: #ffffff;
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  font-size: 0.825rem;
  font-weight: 600;
  color: #334155;
  border: 1px solid #cbd5e1;
  cursor: pointer;
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
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
}
.tab-icon {
  font-size: 0.9rem;
}
.tab-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: 800;
}
.switcher-tab.active .tab-status {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}
.switcher-tab:not(.active) .tab-disetujui {
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
  letter-spacing: 0.05em;
}
.badge-pdf { background: #fee2e2; color: #dc2626; }
.badge-image { background: #e0f2fe; color: #0284c7; }
.badge-meta {
  font-size: 0.75rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
}
.filename-truncate {
  max-width: 200px;
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
.tool-text {
  font-size: 0.8rem;
}

/* File Preview Container - Instant Direct Rendering */
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

/* Image Viewport */
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

/* PDF Viewport */
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

/* Error Overlay if Image fails */
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
.error-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}
.error-actions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
}
.btn-sm {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
}
.btn-retry {
  background: #3b82f6;
  color: #fff;
  border: none;
}
.btn-open {
  background: #334155;
  color: #f1f5f9;
}

/* Unsupported File */
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
  margin: 0 auto 1.5rem auto;
}
.download-link-btn {
  display: inline-block;
  padding: 0.6rem 1.25rem;
  background: #2563eb;
  color: #fff;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
}

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

/* Verification Form */
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
.required {
  color: #ef4444;
}
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
.radio-card input {
  margin-top: 0.2rem;
}
.radio-content {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}
.radio-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
}
.radio-desc {
  font-size: 0.75rem;
  color: #64748b;
}
.valid-card.selected {
  border-color: #10b981;
  background: #ecfdf5;
}
.valid-card.selected .radio-title {
  color: #065f46;
}
.invalid-card.selected {
  border-color: #ef4444;
  background: #fef2f2;
}
.invalid-card.selected .radio-title {
  color: #991b1b;
}

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
.submit-btn:hover:not(:disabled) {
  background: #1e293b;
}
.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
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
.verified-header .panel-card-title {
  margin: 0;
}
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
.btn-change-status:hover {
  background: #e2e8f0;
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
  box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.25rem;
  background: #1e293b;
  border-bottom: 1px solid #334155;
}
.modal-title {
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
}
.modal-meta {
  color: #94a3b8;
  font-size: 0.775rem;
}
.modal-actions {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
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
.modal-pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
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
  .doc-switcher-bar {
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
