<template>
  <div class="page-grid">
    <PageCard title="Daftar Tugas Kelas">
      <template #body>
        <p class="description">Berikut adalah tugas-tugas yang diberikan oleh guru Anda untuk <strong>Kelas {{ tingkatClass || '...' }}</strong>.</p>
        
        <div v-if="loading" class="loading-state">Memuat tugas...</div>
        
        <div class="table-responsive" v-else>
          <table class="table-tugas">
            <thead>
              <tr>
                <th>Judul Tugas</th>
                <th>Mata Pelajaran</th>
                <th>Keterangan</th>
                <th>Oleh Guru</th>
                <th>Tenggat Waktu</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="assignments.length === 0">
                <td colspan="7" class="text-center">Belum ada tugas untuk kelas Anda.</td>
              </tr>
              <tr v-for="task in assignments" :key="task.id">
                <td><strong>{{ task.title }}</strong></td>
                <td><span class="subject-badge">{{ task.subject_name }}</span></td>
                <td class="td-desc">{{ task.description.length > 50 ? task.description.substring(0, 50) + '...' : task.description }}</td>
                <td>{{ task.guru?.name }}</td>
                <td>{{ task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID') : '-' }}</td>
                <td>
                  <span :class="['status-badge', task.submission ? 'status-submitted' : 'status-pending']">
                    {{ task.submission ? 'Selesai' : 'Belum' }}
                  </span>
                </td>
                <td>
                  <button class="action-btn" @click="openSubmitModal(task)" v-if="!task.submission">
                    Kerjakan
                  </button>
                  <button class="action-btn outline" @click="viewSubmission(task)" v-else>
                    Lihat
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </PageCard>

    <!-- Modal Kerjakan / Lihat Tugas -->
    <div class="modal-overlay" v-if="showModal && activeTask">
      <div class="modal">
        <h3>{{ activeTask.submission ? 'Jawaban Anda' : 'Kerjakan Tugas' }}</h3>
        <h4>{{ activeTask.title }}</h4>
        <p class="modal-desc">{{ activeTask.description }}</p>
        
        <form @submit.prevent="submitTask" v-if="!activeTask.submission">
          <div class="form-group">
            <label>Jawaban / Keterangan</label>
            <textarea v-model="submissionContent" required rows="6" placeholder="Ketik jawaban Anda di sini..."></textarea>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showModal = false">Batal</button>
            <button type="submit" class="primary-btn" :disabled="saving">
              {{ saving ? 'Mengirim...' : 'Kumpulkan Tugas' }}
            </button>
          </div>
        </form>
        
        <div v-else>
          <div class="form-group">
            <label>Jawaban Anda (Dikumpulkan pada {{ new Date(activeTask.submission.created_at).toLocaleString('id-ID') }}):</label>
            <div class="submitted-content">{{ activeTask.submission.content }}</div>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showModal = false">Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PageCard from '@/components/PageCard.vue';
import { authApi } from '@/api/auth';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });

const assignments = ref([]);
const tingkatClass = ref('');
const loading = ref(false);

const showModal = ref(false);
const activeTask = ref(null);
const submissionContent = ref('');
const saving = ref(false);

const loadAssignments = async () => {
  loading.value = true;
  try {
    const res = await authApi.fetch('/siswa/tugas');
    assignments.value = res.assignments || [];
    tingkatClass.value = res.tingkat || '';
  } catch (error) {
    console.error("Gagal memuat tugas", error);
  } finally {
    loading.value = false;
  }
};

const openSubmitModal = (task) => {
  activeTask.value = task;
  submissionContent.value = '';
  showModal.value = true;
};

const viewSubmission = (task) => {
  activeTask.value = task;
  showModal.value = true;
};

const submitTask = async () => {
  saving.value = true;
  try {
    await authApi.fetch(`/siswa/tugas/${activeTask.value.id}/submit`, {
      method: 'POST',
      body: JSON.stringify({ content: submissionContent.value })
    });
    
    alert('Tugas berhasil dikumpulkan!');
    showModal.value = false;
    loadAssignments(); // Refresh list to update status
  } catch (error) {
    console.error('Gagal mengumpulkan tugas', error);
    alert('Terjadi kesalahan saat mengumpulkan tugas.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadAssignments();
});
</script>

<style scoped>
.description { margin-bottom: 1.5rem; color: #64748b; }
.loading-state, .empty-state { text-align: center; padding: 2rem; color: #64748b; }

.table-responsive { overflow-x: auto; margin-top: 1rem; }
.table-tugas { width: 100%; border-collapse: collapse; min-width: 800px; }
.table-tugas th, .table-tugas td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.875rem; }
.table-tugas th { background: #f8fafc; font-weight: 600; color: #475569; }
.td-desc { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #64748b; }
.subject-badge { background: #e0f2fe; color: #0369a1; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 1rem; font-weight: 500; }

.status-badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 500; }
.status-pending { background: #fef3c7; color: #b45309; }
.status-submitted { background: #dcfce3; color: #15803d; }

.action-btn { background: #3b82f6; color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 500; }
.action-btn:hover { background: #2563eb; }
.action-btn.outline { background: transparent; border: 1px solid #3b82f6; color: #3b82f6; }
.action-btn.outline:hover { background: #eff6ff; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: white; border-radius: 12px; padding: 1.5rem;
  width: 90%; max-width: 500px;
}
.modal h3 { margin-bottom: 0.25rem; font-size: 1.125rem; color: #0f172a; }
.modal h4 { color: #3b82f6; margin-bottom: 0.75rem; }
.modal-desc { margin-bottom: 1rem; color: #475569; font-size: 0.875rem; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #475569; font-weight: 500; font-size: 0.875rem; }
.form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.875rem; }
.submitted-content { padding: 0.75rem; background: #f1f5f9; border-radius: 6px; white-space: pre-wrap; font-size: 0.875rem; }

.modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-cancel { background: white; border: 1px solid #cbd5e1; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }
.btn-cancel:hover { background: #f8fafc; }
.primary-btn { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; }
.primary-btn:hover:not(:disabled) { background: #2563eb; }
</style>
