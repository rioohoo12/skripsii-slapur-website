<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Tugas Kelas</h2>
          <p>Berikan tugas kepada siswa sesuai dengan mata pelajaran dan tingkat kelas.</p>
        </div>
        <button class="primary-btn" @click="showAddModal = true">
          + Buat Tugas
        </button>
      </div>
      
      <div v-if="loading" class="loading-state">Memuat tugas...</div>
      
      <div class="table-wrap" v-else>
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Judul Tugas</th>
              <th>Mata Pelajaran</th>
              <th>Tingkat Kelas</th>
              <th>Tenggat Waktu</th>
              <th>Jumlah Pengumpulan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="assignments.length === 0">
              <td colspan="7" class="text-center">Belum ada tugas yang dibuat.</td>
            </tr>
            <tr v-for="(task, index) in assignments" :key="task.id">
              <td>{{ index + 1 }}</td>
              <td>{{ task.title }}</td>
              <td>{{ task.subject_name }}</td>
              <td>Kelas {{ task.tingkat }}</td>
              <td>{{ task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID') : 'Tidak ada' }}</td>
              <td>{{ task.submissions_count }} Siswa</td>
              <td>
                <button class="action-btn" @click="viewSubmissions(task)">Lihat Pengumpulan</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Modal Buat Tugas -->
    <div class="modal-overlay" v-if="showAddModal">
      <div class="modal">
        <h3>Buat Tugas Baru</h3>
        
        <form @submit.prevent="submitTask">
          <div class="form-group">
            <label>Tingkat Kelas</label>
            <select v-model="newTask.tingkat" required>
              <option value="" disabled>Pilih Kelas</option>
              <option v-for="cls in availableClasses" :key="cls.tingkat" :value="cls.tingkat">
                {{ cls.name }}
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Judul Tugas</label>
            <input type="text" v-model="newTask.title" required placeholder="Contoh: Merangkum Bab 1" />
          </div>
          
          <div class="form-group">
            <label>Detail / Keterangan Tugas</label>
            <textarea v-model="newTask.description" required rows="4" placeholder="Jelaskan apa yang harus dikerjakan siswa..."></textarea>
          </div>
          
          <div class="form-group">
            <label>Tenggat Waktu (Opsional)</label>
            <input type="date" v-model="newTask.due_date" />
          </div>
          
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showAddModal = false">Batal</button>
            <button type="submit" class="primary-btn" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan Tugas' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Modal Lihat Pengumpulan -->
    <div class="modal-overlay" v-if="showSubmissionsModal && activeTask">
      <div class="modal large">
        <h3>Pengumpulan Tugas: {{ activeTask.title }}</h3>
        
        <div v-if="loadingSubmissions" class="loading-state">Memuat pengumpulan...</div>
        
        <div class="table-wrap" v-else>
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Jawaban / Konten</th>
                <th>Waktu Pengumpulan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="taskSubmissions.length === 0">
                <td colspan="4" class="text-center">Belum ada siswa yang mengumpulkan.</td>
              </tr>
              <tr v-for="(sub, idx) in taskSubmissions" :key="sub.id">
                <td>{{ idx + 1 }}</td>
                <td>{{ sub.siswa?.name || 'Anonim' }}</td>
                <td class="text-pre-wrap">{{ sub.content }}</td>
                <td>{{ new Date(sub.created_at).toLocaleString('id-ID') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="modal-actions" style="margin-top: 1rem;">
          <button type="button" class="btn-cancel" @click="showSubmissionsModal = false">Tutup</button>
        </div>
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

const assignments = ref([]);
const loading = ref(false);
const saving = ref(false);

// Add task modal
const showAddModal = ref(false);
const availableClasses = ref([]);
const newTask = ref({
  tingkat: '',
  title: '',
  description: '',
  due_date: ''
});

// View submissions modal
const showSubmissionsModal = ref(false);
const activeTask = ref(null);
const taskSubmissions = ref([]);
const loadingSubmissions = ref(false);

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
};

const loadAssignments = async () => {
  loading.value = true;
  try {
    const res = await authApi.fetch('/guru/tugas');
    assignments.value = res.assignments || [];
  } catch (error) {
    console.error("Gagal memuat daftar tugas", error);
    alert("Gagal memuat tugas.");
  } finally {
    loading.value = false;
  }
};

const submitTask = async () => {
  saving.value = true;
  try {
    const payload = { ...newTask.value };
    if (!payload.due_date) delete payload.due_date;
    
    await authApi.fetch('/guru/tugas', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    
    alert('Tugas berhasil dibuat!');
    showAddModal.value = false;
    newTask.value = { tingkat: '', title: '', description: '', due_date: '' };
    loadAssignments(); // Refresh
  } catch (error) {
    console.error("Gagal menyimpan tugas", error);
    let msg = error.message || "Gagal menyimpan tugas.";
    if (error.errors) {
      msg += '\n' + JSON.stringify(error.errors);
    }
    alert(msg);
  } finally {
    saving.value = false;
  }
};

const viewSubmissions = async (task) => {
  activeTask.value = task;
  showSubmissionsModal.value = true;
  loadingSubmissions.value = true;
  
  try {
    const res = await authApi.fetch(`/guru/tugas/${task.id}/submissions`);
    taskSubmissions.value = res.submissions || [];
  } catch (error) {
    console.error("Gagal memuat data pengumpulan", error);
    alert("Gagal memuat data pengumpulan.");
    showSubmissionsModal.value = false;
  } finally {
    loadingSubmissions.value = false;
  }
};

onMounted(() => {
  initAvailableClasses();
  loadAssignments();
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

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
th { background: #f8fafc; color: #475569; font-weight: 600; padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
td { padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
.text-center { text-align: center; }
.text-pre-wrap { white-space: pre-wrap; }

.primary-btn { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
.primary-btn:hover:not(:disabled) { background: #2563eb; }
.primary-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.action-btn { background: #f1f5f9; color: #3b82f6; border: 1px solid #cbd5e1; padding: 0.25rem 0.75rem; border-radius: 4px; cursor: pointer; }
.action-btn:hover { background: #e2e8f0; }

.loading-state, .empty-state {
  text-align: center; padding: 2rem; color: #64748b;
}

/* Modals */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: white; border-radius: 12px; padding: 1.5rem;
  width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;
}
.modal.large { max-width: 800px; }
.modal h3 { margin-bottom: 1.25rem; font-size: 1.125rem; color: #0f172a; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #475569; font-weight: 500; font-size: 0.875rem; }
.form-group select, .form-group input, .form-group textarea {
  width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.875rem;
}

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }
.btn-cancel:hover { background: #f8fafc; }
</style>
