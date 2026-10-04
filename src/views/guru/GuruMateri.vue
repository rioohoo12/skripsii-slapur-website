<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Materi Pembelajaran</h2>
          <p>Upload materi PDF/PPT, bagikan ke kelas, dan atur versi terbaru.</p>
        </div>
        <button class="primary-btn" type="button" @click="showAddModal = true">+ Upload Materi</button>
      </div>

      <div class="filters">
        <label>Pilih Kelas untuk melihat materi: </label>
        <select v-model="selectedClass" @change="loadMaterials" class="class-select">
          <option v-for="cls in availableClasses" :key="cls.tingkat" :value="cls.tingkat">
            {{ cls.name }}
          </option>
        </select>
      </div>

      <div v-if="loading" class="loading-state">Memuat materi...</div>
      <div v-else-if="materials.length === 0" class="empty-state">
        Belum ada materi untuk kelas ini.
      </div>
      <div v-else class="material-grid">
        <article v-for="item in materials" :key="item.id" class="material-card">
          <p class="title">{{ item.title }}</p>
          <p class="meta">Kelas {{ item.tingkat }} - {{ item.subject_name }}</p>
          <div class="foot">
            <span class="type-badge">{{ item.type }}</span>
            <div class="actions">
              <span class="date">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</span>
              <a v-if="item.file_url" :href="item.file_url" target="_blank" class="ghost-btn">Lihat</a>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- Modal Upload Materi -->
    <div class="modal-overlay" v-if="showAddModal">
      <div class="modal">
        <h3>Upload Materi Baru</h3>
        
        <form @submit.prevent="submitMaterial">
          <div class="form-group">
            <label>Tingkat Kelas</label>
            <select v-model="newMaterial.tingkat" required>
              <option value="" disabled>Pilih Kelas</option>
              <option v-for="cls in availableClasses" :key="cls.tingkat" :value="cls.tingkat">
                {{ cls.name }}
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Judul Materi</label>
            <input type="text" v-model="newMaterial.title" required placeholder="Contoh: Bab 1 - Pengenalan" />
          </div>
          
          <div class="form-group">
            <label>Tipe Materi</label>
            <select v-model="newMaterial.type" required>
              <option value="PDF">PDF</option>
              <option value="PPT">PPT / Slide</option>
              <option value="Video">Video</option>
              <option value="Link">Tautan (Link)</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Metode Upload</label>
            <div class="radio-group">
              <label><input type="radio" v-model="uploadMethod" value="file" /> Upload File (PDF/PPT/dll)</label>
              <label><input type="radio" v-model="uploadMethod" value="link" /> Masukkan Link (Tautan)</label>
            </div>
          </div>
          
          <div class="form-group" v-if="uploadMethod === 'file'">
            <label>Pilih File</label>
            <input type="file" @change="handleFileUpload" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.mp4,.zip,.rar" required />
          </div>
          
          <div class="form-group" v-if="uploadMethod === 'link'">
            <label>URL / Tautan File</label>
            <input type="url" v-model="newMaterial.file_url" placeholder="https://drive.google.com/..." required />
            <small class="help-text">Masukkan link Google Drive atau link sumber file lainnya.</small>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showAddModal = false">Batal</button>
            <button type="submit" class="primary-btn" :disabled="saving">
              {{ saving ? 'Mengupload...' : 'Upload Materi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { authApi } from '@/api/auth';

const user = computed(() => {
  try { return JSON.parse(localStorage.getItem('user') || '{}'); } catch { return {}; }
});

const materials = ref([]);
const loading = ref(false);
const saving = ref(false);

const selectedClass = ref('');
const availableClasses = ref([]);
const showAddModal = ref(false);
const uploadMethod = ref('file');
const selectedFile = ref(null);
const newMaterial = ref({
  tingkat: '',
  title: '',
  type: 'PDF',
  file_url: ''
});

const initAvailableClasses = () => {
  const j = user.value?.jenjang_guru;
  let classes = [];
  if (j === 'smp' || j === 'smp_sma') {
    classes.push({ tingkat: 7, name: 'Kelas 7 (SMP)' });
    classes.push({ tingkat: 8, name: 'Kelas 8 (SMP)' });
    classes.push({ tingkat: 9, name: 'Kelas 9 (SMP)' });
  }
  if (j === 'sma' || j === 'smp_sma') {
    classes.push({ tingkat: 10, name: 'Kelas 10 (SMA)' });
    classes.push({ tingkat: 11, name: 'Kelas 11 (SMA)' });
    classes.push({ tingkat: 12, name: 'Kelas 12 (SMA)' });
  }
  availableClasses.value = classes;
  if (classes.length > 0) {
    selectedClass.value = classes[0].tingkat;
  }
};

const loadMaterials = async () => {
  if (!selectedClass.value) return;
  loading.value = true;
  try {
    const res = await authApi.fetch(`/guru/materials/${selectedClass.value}`);
    materials.value = res.materials || [];
  } catch (error) {
    console.error("Gagal memuat materi", error);
  } finally {
    loading.value = false;
  }
};

const handleFileUpload = (event) => {
  selectedFile.value = event.target.files[0];
};

const submitMaterial = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('tingkat', newMaterial.value.tingkat);
    formData.append('title', newMaterial.value.title);
    formData.append('type', newMaterial.value.type);
    
    if (uploadMethod.value === 'file' && selectedFile.value) {
      formData.append('file', selectedFile.value);
    } else if (uploadMethod.value === 'link') {
      formData.append('file_url', newMaterial.value.file_url);
    }
    
    await authApi.fetch('/guru/materials', {
      method: 'POST',
      body: formData,
      headers: {
        // Jangan set 'Content-Type': 'application/json' karena ini FormData
      }
    });
    
    alert('Materi berhasil diupload!');
    showAddModal.value = false;
    
    // Refresh list if we just uploaded to the currently selected class
    if (String(newMaterial.value.tingkat) === String(selectedClass.value)) {
      loadMaterials();
    }
    
    newMaterial.value = {
      tingkat: '',
      title: '',
      type: 'PDF',
      file_url: ''
    };
    selectedFile.value = null;
    uploadMethod.value = 'file';
  } catch (error) {
    console.error("Gagal menyimpan materi", error);
    alert("Gagal mengupload materi. Jika file sangat besar, pastikan server mendukung ukuran tersebut.");
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  initAvailableClasses();
  if (selectedClass.value) {
    loadMaterials();
  }
});
</script>

<style scoped>
.page-grid { padding: 1.5rem; }
.panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}
.head h2 { color: #1e293b; font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; }
.head p { color: #64748b; font-size: 0.875rem; }

.filters {
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.class-select {
  padding: 0.5rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.9rem;
}

.loading-state, .empty-state {
  text-align: center; padding: 2rem; color: #64748b;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e1;
}

.material-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}
.material-card {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  transition: all 0.2s ease;
}
.material-card:hover {
  border-color: #cbd5e1;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.title {
  margin: 0;
  color: #0f172a;
  font-weight: 600;
  font-size: 1.1rem;
  line-height: 1.4;
}
.meta {
  margin: 0.5rem 0 0;
  font-size: 0.85rem;
  color: #64748b;
}
.foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}
.type-badge {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}
.actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.date {
  font-size: 0.75rem;
  color: #94a3b8;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #3b82f6;
  font-weight: 500;
  border-radius: 6px;
  padding: 0.3rem 0.8rem;
  cursor: pointer;
  text-decoration: none;
  font-size: 0.85rem;
  transition: all 0.2s;
}
.ghost-btn:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
}

.primary-btn { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
.primary-btn:hover:not(:disabled) { background: #2563eb; }
.primary-btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* Modals */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: white; border-radius: 12px; padding: 1.5rem;
  width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;
}
.modal h3 { margin-top: 0; margin-bottom: 1.25rem; font-size: 1.125rem; color: #0f172a; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #475569; font-weight: 500; font-size: 0.875rem; }
.form-group select, .form-group input[type="text"], .form-group input[type="url"] {
  width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.875rem;
}
.form-group input[type="file"] {
  width: 100%; padding: 0.5rem 0; font-size: 0.875rem;
}
.radio-group {
  display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.2rem;
}
.radio-group label {
  display: flex; align-items: center; gap: 0.4rem; font-weight: normal; cursor: pointer; color: #334155;
}
.form-group input:focus, .form-group select:focus {
  outline: none;
  border-color: #3b82f6;
}
.help-text {
  display: block;
  margin-top: 0.4rem;
  color: #64748b;
  font-size: 0.75rem;
}

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
.btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }
.btn-cancel:hover { background: #f8fafc; }
</style>
