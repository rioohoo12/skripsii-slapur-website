<template>
  <div class="dokumen-page" :class="themeClass">
    <!-- Hero Header Card -->
    <div class="hero-card" :class="isPutri ? 'hero-putri' : 'hero-putra'">
      <div class="hero-content">
        <div class="hero-badge">
          📁 BERKAS ADMINISTRASI PENDAFTARAN
        </div>
        <h1 class="hero-title">Dokumen & Riwayat Pendaftaran</h1>
        <p class="hero-desc">
          Pantau kelengkapan berkas pendaftaran Anda, lihat status verifikasi dari Staff Administrasi, dan periksa riwayat dokumen yang telah diunggah.
        </p>

        <!-- Progress Kelengkapan -->
        <div class="progress-wrap">
          <div class="progress-header">
            <span>Kelengkapan Berkas Wajib: <strong>{{ uploadedCount }} / {{ requiredDocs.length }} Selesai</strong></span>
            <span class="progress-percent">{{ progressPercentage }}%</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
          </div>
        </div>
      </div>

      <div class="hero-actions">
        <button class="btn-hero-upload" @click="openUploadModal()">
          <span class="btn-icon">📤</span>
          <span>Unggah Dokumen</span>
        </button>
        <button class="btn-hero-refresh" @click="loadDokumen" :disabled="loading" title="Refresh Data">
          <span class="btn-icon" :class="{ 'spin-anim': loading }">🔄</span>
          <span>{{ loading ? 'Memuat...' : 'Segarkan' }}</span>
        </button>
      </div>
    </div>

    <!-- Tab Filter / Tampilan -->
    <div class="tab-bar">
      <button 
        type="button" 
        class="tab-btn" 
        :class="{ active: activeTab === 'checklist' }"
        @click="activeTab = 'checklist'"
      >
        <span class="tab-icon">📋</span>
        <span>Checklist Berkas Wajib</span>
        <span class="tab-count">{{ uploadedCount }}/{{ requiredDocs.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-btn" 
        :class="{ active: activeTab === 'riwayat' }"
        @click="activeTab = 'riwayat'"
      >
        <span class="tab-icon">🕒</span>
        <span>Riwayat Semua Dokumen</span>
        <span class="tab-count">{{ daftarDokumen.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-btn" 
        :class="{ active: activeTab === 'ai' }"
        @click="activeTab = 'ai'"
      >
        <span class="tab-icon">🤖</span>
        <span>Asisten AI Dokumen</span>
      </button>
    </div>

    <!-- Alert Notifikasi Feedback -->
    <Transition name="fade">
      <div v-if="successMsg" class="alert-box success">
        <span class="alert-icon">✅</span>
        <span class="alert-text">{{ successMsg }}</span>
        <button class="alert-close" @click="successMsg = ''">✕</button>
      </div>
    </Transition>
    <Transition name="fade">
      <div v-if="errorMsg" class="alert-box error">
        <span class="alert-icon">⚠️</span>
        <span class="alert-text">{{ errorMsg }}</span>
        <button class="alert-close" @click="errorMsg = ''">✕</button>
      </div>
    </Transition>

    <!-- SECTION 1: Checklist Berkas Wajib Cards -->
    <div v-if="activeTab === 'checklist'" class="section-container">
      <div class="section-head">
        <div>
          <h2 class="section-title">Dokumen Wajib Pendaftaran</h2>
          <p class="section-subtitle">
            Pastikan ke-4 dokumen wajib ini telah diunggah dan terverifikasi untuk melanjutkan pendaftaran santri.
          </p>
        </div>
      </div>

      <div class="docs-grid">
        <div 
          v-for="item in requiredDocs" 
          :key="item.key" 
          class="doc-card"
          :class="['status-' + item.statusKey]"
        >
          <div class="doc-card-top">
            <div class="doc-icon-wrap" :class="'icon-' + item.statusKey">
              <span>{{ item.icon }}</span>
            </div>
            <div class="status-badge" :class="'badge-' + item.statusKey">
              <span class="status-dot">●</span>
              <span>{{ item.statusText }}</span>
            </div>
          </div>

          <h3 class="doc-title">{{ item.title }}</h3>
          <p class="doc-desc">{{ item.desc }}</p>

          <!-- File info if uploaded -->
          <div v-if="item.uploadedDoc" class="uploaded-snippet">
            <div class="file-name-row">
              <span class="file-icon">📄</span>
              <span class="file-name" :title="item.uploadedDoc.fileName">
                {{ item.uploadedDoc.fileName || item.title + '.pdf' }}
              </span>
            </div>
            <div class="upload-time">
              Diunggah: {{ item.uploadedDoc.tanggal }}
            </div>
            <!-- Catatan staff jika ada -->
            <div v-if="item.uploadedDoc.catatan" class="staff-notes">
              <strong>Catatan Staff:</strong> {{ item.uploadedDoc.catatan }}
            </div>
          </div>

          <!-- Card Actions -->
          <div class="doc-card-actions">
            <a 
              v-if="item.uploadedDoc?.fileUrl" 
              :href="item.uploadedDoc.fileUrl" 
              target="_blank" 
              rel="noopener noreferrer"
              class="btn-card-action btn-view"
            >
              👁️ Lihat Berkas
            </a>
            
            <button 
              type="button" 
              class="btn-card-action"
              :class="item.uploadedDoc ? 'btn-reupload' : 'btn-upload-primary'"
              @click="openUploadModal(item.key)"
            >
              {{ item.uploadedDoc ? '🔄 Unggah Ulang' : '📤 Unggah Sekarang' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 2: Riwayat Dokumen Table -->
    <div v-else-if="activeTab === 'riwayat'" class="section-container">
      <div class="section-head">
        <div>
          <h2 class="section-title">Riwayat Dokumen Terunggah</h2>
          <p class="section-subtitle">
            Daftar lengkap seluruh berkas yang Anda kirimkan beserta status verifikasi administrasi.
          </p>
        </div>
        <button class="btn-primary-sm" @click="openUploadModal()">
          + Unggah Dokumen Baru
        </button>
      </div>

      <div v-if="loading && daftarDokumen.length === 0" class="loading-state">
        <div class="spinner"></div>
        <p>Memuat riwayat berkas pendaftaran...</p>
      </div>

      <div v-else-if="daftarDokumen.length === 0" class="empty-state-card">
        <div class="empty-icon">📂</div>
        <h3>Belum Ada Dokumen yang Diunggah</h3>
        <p>Anda belum mengunggah dokumen pendaftaran apa pun. Klik tombol di bawah untuk mengunggah dokumen pertama Anda.</p>
        <button class="btn-primary" @click="openUploadModal()">
          Unggah Dokumen Pendaftaran
        </button>
      </div>

      <div v-else class="table-card">
        <div class="table-responsive">
          <table class="history-table">
            <thead>
              <tr>
                <th style="width: 50px;">No</th>
                <th>Jenis Dokumen</th>
                <th>Nama Berkas</th>
                <th>Waktu Unggah</th>
                <th>Status Verifikasi</th>
                <th>Catatan / Keterangan</th>
                <th style="text-align: right; width: 170px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(doc, idx) in daftarDokumen" :key="doc.id || idx">
                <td class="text-muted">{{ idx + 1 }}</td>
                <td>
                  <div class="type-cell">
                    <span class="type-icon">{{ getDocIcon(doc.jenisKey) }}</span>
                    <div>
                      <strong class="type-name">{{ doc.tipe }}</strong>
                      <span v-if="isDocWajib(doc.jenisKey)" class="badge-wajib">Wajib</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="filename-cell">
                    <span class="file-symbol">📄</span>
                    <span class="file-text" :title="doc.fileName">{{ doc.fileName || doc.tipe }}</span>
                  </div>
                </td>
                <td class="text-muted font-sm">
                  {{ doc.tanggal }}
                </td>
                <td>
                  <span class="status-pill" :class="'pill-' + doc.statusRaw">
                    <span class="dot">●</span>
                    {{ doc.status }}
                  </span>
                </td>
                <td>
                  <span v-if="doc.catatan" class="note-text">
                    💬 {{ doc.catatan }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
                <td style="text-align: right;">
                  <div class="action-btn-group">
                    <a 
                      v-if="doc.fileUrl" 
                      :href="doc.fileUrl" 
                      target="_blank" 
                      rel="noopener noreferrer"
                      class="btn-tbl-action btn-tbl-view"
                      title="Lihat Dokumen"
                    >
                      👁️ Buka
                    </a>
                    <button 
                      type="button" 
                      class="btn-tbl-action btn-tbl-replace"
                      @click="openUploadModal(doc.jenisKey)"
                      title="Ganti Dokumen"
                    >
                      🔄 Ganti
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION 3: Asisten AI Dokumen -->
    <div v-else-if="activeTab === 'ai'" class="section-container">
      <div class="ai-assistant-wrapper">
        <div class="ai-banner">
          <div class="ai-banner-icon">🤖</div>
          <div class="ai-banner-text">
            <h3>Asisten Dokumen Cerdas SLAPUR</h3>
            <p>
              Gunakan panduan interaktif kami untuk mengetahui syarat berkas, mendapatkan saran format file, atau bertanya seputar proses verifikasi administrasi santri.
            </p>
          </div>
        </div>

        <div class="ai-grid">
          <div class="ai-card">
            <h4>💡 Panduan Format Berkas</h4>
            <ul>
              <li><strong>Ukuran Maksimal:</strong> 5 MB per file.</li>
              <li><strong>Format yang Didukung:</strong> PDF, JPG, JPEG, PNG, dan WebP.</li>
              <li><strong>Kualitas Dokumen:</strong> Pastikan teks pada scan Akte dan KK terbaca jelas dan tidak terpotong.</li>
              <li><strong>Pas Foto:</strong> Format foto resmi dengan latar belakang polos dan pencahayaan terang.</li>
            </ul>
          </div>

          <div class="ai-card">
            <h4>⚡ Bantuan Cepat AI Chatbot</h4>
            <p>
              Punya pertanyaan atau ingin AI membaca dan memverifikasi kesiapan berkas Anda secara otomatis?
            </p>
            <button class="btn-ask-ai" @click="openGlobalChatbot">
              💬 Buka Chatbot Pendaftaran & Verifikasi Dokumen
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL UNGGAH DOKUMEN -->
    <Transition name="modal-fade">
      <div v-if="showModal" class="modal-overlay" @click.self="closeUploadModal">
        <div class="modal-card">
          <div class="modal-header">
            <div class="modal-title-group">
              <h3>📤 Unggah Dokumen Pendaftaran</h3>
              <p class="modal-subtitle">Pilih jenis dokumen dan berkas yang akan disimpan ke arsip administrasi.</p>
            </div>
            <button class="btn-modal-close" @click="closeUploadModal">✕</button>
          </div>

          <form @submit.prevent="submitUpload">
            <div class="modal-body">
              <div v-if="modalError" class="modal-alert-error">
                ⚠️ {{ modalError }}
              </div>

              <!-- Pilih Jenis Dokumen -->
              <div class="form-group">
                <label class="form-label">
                  Jenis Dokumen <span class="required-star">*</span>
                </label>
                <select v-model="uploadForm.jenis" class="form-select" required>
                  <option value="">— Pilih Jenis Dokumen —</option>
                  <option value="kk">Kartu Keluarga (KK) [Wajib]</option>
                  <option value="akte_kelahiran">Akte Kelahiran [Wajib]</option>
                  <option value="pas_foto">Pas Foto Resmi [Wajib]</option>
                  <option value="raport">Rapor / Ijazah [Wajib]</option>
                  <option value="lainnya">Dokumen Tambahan / Lainnya</option>
                </select>
              </div>

              <!-- Dropzone File Upload -->
              <div class="form-group">
                <label class="form-label">
                  Pilih File Berkas <span class="required-star">*</span>
                </label>
                
                <div 
                  class="file-dropzone" 
                  :class="{ 'has-file': uploadForm.file, 'is-dragging': isDragging }"
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="onFileDrop"
                  @click="triggerFileInput"
                >
                  <input 
                    type="file" 
                    ref="fileInputRef" 
                    class="hidden-file-input"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    @change="onFileSelected"
                  />

                  <div v-if="!uploadForm.file" class="dropzone-empty">
                    <span class="dropzone-icon">☁️</span>
                    <p class="dropzone-text"><strong>Klik untuk memilih file</strong> atau seret file ke sini</p>
                    <span class="dropzone-hint">Format didukung: PDF, JPG, PNG, WebP (Maksimal 5MB)</span>
                  </div>

                  <div v-else class="dropzone-selected">
                    <span class="selected-file-icon">{{ isPdf(uploadForm.file) ? '📄' : '🖼️' }}</span>
                    <div class="selected-file-info">
                      <span class="selected-filename">{{ uploadForm.file.name }}</span>
                      <span class="selected-filesize">{{ formatFileSize(uploadForm.file.size) }}</span>
                    </div>
                    <button type="button" class="btn-clear-file" @click.stop="clearFile">✕ Hapus</button>
                  </div>
                </div>
              </div>

              <div class="upload-tips">
                💡 <em>Setelah diunggah, Staff Administrasi akan memverifikasi berkas Anda. Jika dokumen ditolak, Anda dapat mengunggah ulang perbaikan di halaman ini.</em>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="closeUploadModal" :disabled="uploading">
                Batal
              </button>
              <button 
                type="submit" 
                class="btn-submit" 
                :class="isPutri ? 'btn-submit-putri' : 'btn-submit-putra'"
                :disabled="uploading || !uploadForm.file || !uploadForm.jenis"
              >
                <span v-if="uploading" class="spinner-white"></span>
                <span>{{ uploading ? 'Mengunggah Berkas...' : '🚀 Unggah Dokumen' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { pendaftaranApi } from '@/api/pendaftaran';
import { useAuthStore } from '@/stores/auth';
import { usePendaftaranStore } from '@/stores/pendaftaran';

const props = defineProps({
  jenisKelamin: { type: String, default: '' },
});

const route = useRoute();
const authStore = useAuthStore();
const pendaftaranStore = usePendaftaranStore();

const isPutri = computed(() => {
  if (props.jenisKelamin === 'perempuan') return true;
  if (props.jenisKelamin === 'laki-laki') return false;
  if (route.params.jenisKelamin === 'perempuan') return true;
  if (route.params.jenisKelamin === 'laki-laki') return false;
  return authStore.user?.jenis_kelamin === 'perempuan';
});

const themeClass = computed(() => (isPutri.value ? 'theme-perempuan' : 'theme-laki-laki'));

const activeTab = ref('checklist');
const loading = ref(false);
const daftarDokumen = ref([]);
const successMsg = ref('');
const errorMsg = ref('');

// Modal state
const showModal = ref(false);
const uploading = ref(false);
const modalError = ref('');
const isDragging = ref(false);
const fileInputRef = ref(null);

const uploadForm = reactive({
  jenis: '',
  file: null,
});

function labelJenis(jenis) {
  const labels = {
    kk: 'Kartu Keluarga (KK)',
    akte_kelahiran: 'Akte Kelahiran',
    ijazah: 'Ijazah',
    pas_foto: 'Pas Foto',
    raport: 'Rapor / Ijazah',
    lainnya: 'Dokumen Lainnya',
  };
  return labels[jenis] || jenis;
}

function getDocIcon(jenis) {
  const icons = {
    kk: '👨‍👩‍👦',
    akte_kelahiran: '📜',
    pas_foto: '👤',
    raport: '📖',
    ijazah: '🎓',
    lainnya: '📁',
  };
  return icons[jenis] || '📄';
}

function isDocWajib(jenis) {
  return ['kk', 'akte_kelahiran', 'pas_foto', 'raport'].includes(jenis);
}

function normalizeStatus(rawStatus) {
  const s = String(rawStatus || '').toLowerCase();
  if (s === 'terverifikasi' || s === 'disetujui' || s === 'approved') {
    return { key: 'approved', text: 'Terverifikasi', raw: 'approved' };
  }
  if (s === 'ditolak' || s === 'rejected') {
    return { key: 'rejected', text: 'Ditolak (Perlu Perbaikan)', raw: 'rejected' };
  }
  if (s === 'menunggu' || s === 'pending') {
    return { key: 'pending', text: 'Menunggu Verifikasi', raw: 'pending' };
  }
  return { key: 'pending', text: 'Menunggu Verifikasi', raw: 'pending' };
}

function fmtDate(iso) {
  if (!iso) return '-';
  try {
    const d = new Date(iso);
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return '-';
  }
}

// 4 Dokumen Wajib
const requiredDocsConfig = [
  {
    key: 'kk',
    title: 'Kartu Keluarga (KK)',
    icon: '👨‍👩‍👦',
    desc: 'Scan Kartu Keluarga asli / fotokopi legalisir yang memuat nama santri dan orang tua.',
  },
  {
    key: 'akte_kelahiran',
    title: 'Akte Kelahiran',
    icon: '📜',
    desc: 'Scan Akte Kelahiran santri untuk validasi nama lengkap, tanggal lahir, dan NIK.',
  },
  {
    key: 'pas_foto',
    title: 'Pas Foto Resmi',
    icon: '👤',
    desc: 'Pas foto santri 3x4 formal berwarna dengan pakaian rapi / seragam dan latar belakang polos.',
  },
  {
    key: 'raport',
    title: 'Rapor / Ijazah',
    icon: '📖',
    desc: 'Scan rapor semester terakhir atau surat keterangan lulus / pindahan dari sekolah asal.',
  },
];

const requiredDocs = computed(() => {
  return requiredDocsConfig.map((cfg) => {
    // Cari dokumen yang diunggah untuk jenis ini
    const doc = daftarDokumen.value.find((d) => d.jenisKey === cfg.key);
    if (!doc) {
      return {
        ...cfg,
        uploadedDoc: null,
        statusKey: 'unuploaded',
        statusText: 'Belum Diunggah',
      };
    }
    const st = normalizeStatus(doc.statusRaw);
    return {
      ...cfg,
      uploadedDoc: doc,
      statusKey: st.key,
      statusText: st.text,
    };
  });
});

const uploadedCount = computed(() => {
  return requiredDocs.value.filter((d) => d.uploadedDoc !== null).length;
});

const progressPercentage = computed(() => {
  const total = requiredDocsConfig.length;
  if (!total) return 0;
  return Math.round((uploadedCount.value / total) * 100);
});

async function loadDokumen() {
  loading.value = true;
  errorMsg.value = '';
  try {
    const res = await pendaftaranApi.getDokumen();
    const docs = Array.isArray(res?.documents) ? res.documents : [];
    daftarDokumen.value = docs.map((d) => {
      const st = normalizeStatus(d.status);
      return {
        id: d.id,
        jenisKey: d.jenis,
        tipe: d.nama_dokumen || labelJenis(d.jenis),
        fileName: d.file_name || (d.file_path ? d.file_path.split('/').pop() : ''),
        fileUrl: d.file_url,
        tanggal: fmtDate(d.updated_at || d.created_at),
        status: st.text,
        statusKey: st.key,
        statusRaw: st.raw,
        catatan: d.catatan || '',
        verifikator: d.diverifikasi_oleh || '',
      };
    });
  } catch (e) {
    errorMsg.value = e?.message || 'Gagal memuat dokumen.';
  } finally {
    loading.value = false;
  }
}

function openUploadModal(defaultJenis = '') {
  modalError.value = '';
  uploadForm.jenis = defaultJenis || '';
  uploadForm.file = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
  showModal.value = true;
}

function closeUploadModal() {
  if (uploading.value) return;
  showModal.value = false;
  uploadForm.jenis = '';
  uploadForm.file = null;
}

function triggerFileInput() {
  fileInputRef.value?.click();
}

function onFileSelected(e) {
  const f = e.target.files?.[0];
  handleFile(f);
}

function onFileDrop(e) {
  isDragging.value = false;
  const f = e.dataTransfer?.files?.[0];
  handleFile(f);
}

function handleFile(f) {
  if (!f) return;
  modalError.value = '';
  // Validasi ukuran maks 5MB
  if (f.size > 5 * 1024 * 1024) {
    modalError.value = 'Ukuran file maksimal 5 MB.';
    return;
  }
  uploadForm.file = f;
}

function clearFile() {
  uploadForm.file = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
}

function isPdf(file) {
  return file?.type === 'application/pdf' || file?.name?.toLowerCase().endsWith('.pdf');
}

function formatFileSize(bytes) {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

async function submitUpload() {
  modalError.value = '';
  if (!uploadForm.jenis) {
    modalError.value = 'Silakan pilih jenis dokumen.';
    return;
  }
  if (!uploadForm.file) {
    modalError.value = 'Silakan pilih berkas file untuk diunggah.';
    return;
  }

  uploading.value = true;
  try {
    const res = await pendaftaranApi.uploadDokumen(uploadForm.jenis, uploadForm.file);
    successMsg.value = res?.message || 'Dokumen berhasil diunggah dan disimpan ke riwayat!';
    closeUploadModal();
    await loadDokumen();
    // Refresh status pendaftaran
    pendaftaranStore.fetchStatus();
  } catch (e) {
    modalError.value = e?.message || 'Gagal mengunggah dokumen. Silakan periksa koneksi Anda.';
  } finally {
    uploading.value = false;
  }
}

function openGlobalChatbot() {
  window.dispatchEvent(new CustomEvent('open-chat', { detail: 'saya mau upload dokumen pendaftaran' }));
}

onMounted(() => {
  loadDokumen();
});
</script>

<style scoped>
.dokumen-page {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  padding-bottom: 3rem;
}

/* Hero Header Card */
.hero-card {
  border-radius: 20px;
  padding: 2.25rem 2.5rem;
  color: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
  box-shadow: 0 10px 25px rgba(15, 30, 60, 0.12);
}

.hero-putra {
  background: linear-gradient(135deg, #0f1e3c 0%, #1e3a8a 100%);
}

.hero-putri {
  background: linear-gradient(135deg, #3b0764 0%, #701a75 50%, #831843 100%);
}

.hero-content {
  flex: 1;
  min-width: 320px;
}

.hero-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-bottom: 0.75rem;
}

.hero-title {
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0 0 0.5rem;
  line-height: 1.25;
}

.hero-desc {
  font-size: 0.95rem;
  opacity: 0.9;
  line-height: 1.5;
  margin: 0 0 1.25rem;
  max-width: 600px;
}

.progress-wrap {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  max-width: 480px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  margin-bottom: 0.45rem;
}

.progress-percent {
  font-weight: 700;
}

.progress-track {
  height: 8px;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #10b981;
  border-radius: 999px;
  transition: width 0.4s ease;
}

.hero-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  min-width: 200px;
}

.btn-hero-upload {
  background: #ffffff;
  color: #0f1e3c;
  border: none;
  padding: 0.85rem 1.4rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-hero-upload:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.btn-hero-refresh {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.65rem 1.2rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-hero-refresh:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.25);
}

.spin-anim {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Tab Bar */
.tab-bar {
  display: flex;
  gap: 0.75rem;
  background: #ffffff;
  padding: 0.5rem;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  overflow-x: auto;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: #64748b;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-btn:hover {
  background: #f8fafc;
  color: #1e293b;
}

.tab-btn.active {
  background: #0f1e3c;
  color: #ffffff;
}

.theme-perempuan .tab-btn.active {
  background: #701a75;
}

.tab-count {
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: #e2e8f0;
  color: #334155;
  font-weight: 700;
}

.tab-btn.active .tab-count {
  background: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

/* Alert Boxes */
.alert-box {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
}

.alert-box.success {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

.alert-box.error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.alert-close {
  margin-left: auto;
  background: transparent;
  border: none;
  color: inherit;
  font-weight: bold;
  cursor: pointer;
}

/* Section Containers */
.section-container {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 800;
  color: #0f1e3c;
}

.section-subtitle {
  margin: 0.2rem 0 0;
  font-size: 0.88rem;
  color: #64748b;
}

/* Grid Checklist Cards */
.docs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}

.doc-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1.5px solid #e2e8f0;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
}

.doc-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.doc-card.status-approved {
  border-color: #a7f3d0;
}

.doc-card.status-pending {
  border-color: #fde68a;
}

.doc-card.status-rejected {
  border-color: #fecaca;
}

.doc-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.doc-icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.icon-approved { background: #d1fae5; }
.icon-pending { background: #fef3c7; }
.icon-rejected { background: #fee2e2; }
.icon-unuploaded { background: #f1f5f9; }

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-dot {
  font-size: 0.65rem;
}

.badge-approved { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-rejected { background: #fee2e2; color: #991b1b; }
.badge-unuploaded { background: #f1f5f9; color: #64748b; }

.doc-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.doc-desc {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.45;
  flex: 1;
}

.uploaded-snippet {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.65rem 0.85rem;
  font-size: 0.8rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.file-name-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-weight: 600;
  color: #0f1e3c;
}

.file-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
}

.upload-time {
  color: #94a3b8;
  font-size: 0.75rem;
}

.staff-notes {
  background: #fffbeb;
  border: 1px dashed #fde68a;
  padding: 0.35rem 0.5rem;
  border-radius: 6px;
  color: #92400e;
  font-size: 0.75rem;
}

.doc-card-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-card-action {
  flex: 1;
  padding: 0.6rem 0.85rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
  border: none;
}

.btn-view {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #cbd5e1;
}

.btn-view:hover {
  background: #e2e8f0;
}

.btn-upload-primary {
  background: #0f1e3c;
  color: #ffffff;
}

.theme-perempuan .btn-upload-primary {
  background: #701a75;
}

.btn-upload-primary:hover {
  opacity: 0.9;
}

.btn-reupload {
  background: #f8fafc;
  color: #0f1e3c;
  border: 1px solid #cbd5e1;
}

.btn-reupload:hover {
  background: #f1f5f9;
}

/* History Table Card */
.table-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.history-table th {
  background: #f8fafc;
  padding: 1rem 1.25rem;
  font-size: 0.82rem;
  font-weight: 700;
  color: #475569;
  border-bottom: 1px solid #e2e8f0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.history-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.88rem;
  color: #1e293b;
  vertical-align: middle;
}

.history-table tr:hover {
  background: #f8fafc;
}

.type-cell {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.type-icon {
  font-size: 1.3rem;
}

.type-name {
  display: block;
  color: #0f1e3c;
}

.badge-wajib {
  display: inline-block;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
}

.filename-cell {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: monospace;
  font-size: 0.85rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.status-pill .dot {
  font-size: 0.65rem;
}

.pill-approved {
  background: #d1fae5;
  color: #065f46;
}

.pill-pending {
  background: #fef3c7;
  color: #92400e;
}

.pill-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.note-text {
  font-size: 0.8rem;
  color: #64748b;
  font-style: italic;
}

.action-btn-group {
  display: flex;
  gap: 0.4rem;
  justify-content: flex-end;
}

.btn-tbl-action {
  padding: 0.4rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #334155;
  transition: all 0.15s;
}

.btn-tbl-action:hover {
  background: #f1f5f9;
  color: #0f1e3c;
}

.btn-tbl-view {
  border-color: #93c5fd;
  color: #1d4ed8;
  background: #eff6ff;
}

.btn-tbl-view:hover {
  background: #dbeafe;
}

/* Empty State Card */
.empty-state-card {
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  border-radius: 16px;
  padding: 3.5rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 3.5rem;
  margin-bottom: 0.75rem;
}

.empty-state-card h3 {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f1e3c;
}

.empty-state-card p {
  margin: 0 0 1.5rem;
  color: #64748b;
  font-size: 0.9rem;
}

.btn-primary {
  background: #0f1e3c;
  color: #ffffff;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}

.theme-perempuan .btn-primary {
  background: #701a75;
}

.btn-primary-sm {
  background: #0f1e3c;
  color: #ffffff;
  border: none;
  padding: 0.55rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}

.theme-perempuan .btn-primary-sm {
  background: #701a75;
}

/* AI Assistant Tab */
.ai-assistant-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.ai-banner {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
  color: #ffffff;
  padding: 2rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.ai-banner-icon {
  font-size: 3rem;
}

.ai-banner-text h3 {
  margin: 0 0 0.4rem;
  font-size: 1.35rem;
  font-weight: 800;
}

.ai-banner-text p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.9;
  line-height: 1.5;
}

.ai-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.25rem;
}

.ai-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 1.5rem;
}

.ai-card h4 {
  margin: 0 0 1rem;
  color: #0f1e3c;
  font-size: 1.1rem;
  font-weight: 700;
}

.ai-card ul {
  padding-left: 1.25rem;
  margin: 0;
  color: #475569;
  font-size: 0.88rem;
  line-height: 1.6;
}

.btn-ask-ai {
  margin-top: 1rem;
  background: #4338ca;
  color: #ffffff;
  border: none;
  padding: 0.75rem 1.2rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  width: 100%;
}

.btn-ask-ai:hover {
  background: #3730a3;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.modal-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: modalPop 0.25s ease-out;
}

@keyframes modalPop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal-header {
  padding: 1.5rem 1.75rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.modal-title-group h3 {
  margin: 0 0 0.3rem;
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f1e3c;
}

.modal-subtitle {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
}

.btn-modal-close {
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  color: #64748b;
  cursor: pointer;
}

.btn-modal-close:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.modal-body {
  padding: 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.modal-alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 0.65rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.form-label {
  font-size: 0.88rem;
  font-weight: 700;
  color: #1e293b;
}

.required-star {
  color: #ef4444;
}

.form-select {
  padding: 0.75rem 1rem;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  font-size: 0.92rem;
  color: #1e293b;
  background: #ffffff;
  outline: none;
  transition: border-color 0.2s;
}

.form-select:focus {
  border-color: #0f1e3c;
}

.file-dropzone {
  border: 2px dashed #cbd5e1;
  border-radius: 14px;
  padding: 2rem 1.5rem;
  text-align: center;
  cursor: pointer;
  background: #f8fafc;
  transition: all 0.2s;
}

.file-dropzone:hover,
.file-dropzone.is-dragging {
  border-color: #0f1e3c;
  background: #eff6ff;
}

.file-dropzone.has-file {
  border-style: solid;
  border-color: #10b981;
  background: #f0fdf4;
}

.hidden-file-input {
  display: none;
}

.dropzone-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
}

.dropzone-icon {
  font-size: 2.5rem;
  margin-bottom: 0.3rem;
}

.dropzone-text {
  margin: 0;
  font-size: 0.9rem;
  color: #334155;
}

.dropzone-hint {
  font-size: 0.75rem;
  color: #94a3b8;
}

.dropzone-selected {
  display: flex;
  align-items: center;
  gap: 1rem;
  text-align: left;
}

.selected-file-icon {
  font-size: 2.2rem;
}

.selected-file-info {
  flex: 1;
  overflow: hidden;
}

.selected-filename {
  display: block;
  font-weight: 700;
  font-size: 0.92rem;
  color: #0f1e3c;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.selected-filesize {
  font-size: 0.78rem;
  color: #64748b;
}

.btn-clear-file {
  background: #fee2e2;
  color: #991b1b;
  border: none;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
}

.upload-tips {
  font-size: 0.78rem;
  color: #64748b;
  line-height: 1.45;
}

.modal-footer {
  padding: 1rem 1.75rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-cancel {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 0.7rem 1.25rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
}

.btn-submit {
  border: none;
  padding: 0.7rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.9rem;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-submit-putra {
  background: #0f1e3c;
}

.btn-submit-putri {
  background: #701a75;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-white {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem;
  color: #64748b;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #0f1e3c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@media (max-width: 768px) {
  .hero-card {
    padding: 1.75rem;
  }
  .hero-title {
    font-size: 1.5rem;
  }
  .hero-actions {
    width: 100%;
  }
}

@media (max-width: 640px) {
  .hero-card {
    padding: 1.25rem;
  }
  .docs-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  .doc-card {
    padding: 1.15rem;
  }
  .modal-card {
    width: 95%;
    padding: 1.25rem;
  }
  .history-table th, 
  .history-table td {
    padding: 0.75rem 0.85rem;
    font-size: 0.85rem;
  }
}
</style>
