<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Input Nilai Siswa</h2>
          <p>Kelola nilai tugas, quiz, ujian harian, mid semester, dan final semester.</p>
        </div>
      </div>
      
      <div v-if="loadingClasses" class="loading-state">Memuat kelas...</div>
      <div v-else-if="classes.length === 0" class="empty-state">
        Anda belum memiliki kelas untuk diinput nilainya.
      </div>
      
      <div v-else class="classes-grid">
        <div 
          v-for="kls in classes" 
          :key="kls" 
          class="class-card" 
          @click="openInputModal(kls)"
        >
          <h3>Kelas {{ kls }}</h3>
          <p>Klik untuk Input Nilai</p>
        </div>
      </div>
    </section>

    <!-- Modal Input Nilai -->
    <div class="modal-overlay" v-if="showModal && activeClass">
      <div class="modal large-modal">
        <h3>Input Nilai - Kelas {{ activeClass }}</h3>
        
        <div v-if="loadingScores" class="loading-state">Memuat data nilai...</div>
        
        <form @submit.prevent="saveScores" v-else>
          <div class="table-wrap">
            <table class="nilai-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Siswa</th>
                  <th v-for="task in assignmentsList" :key="task.id" class="col-input" style="min-width: 120px;">
                    {{ task.title }}
                  </th>
                  <th class="col-input" title="Rata-rata dari semua tugas di atas">Rata-rata Tugas</th>
                  <th class="col-input">Quiz</th>
                  <th class="col-input">Harian</th>
                  <th class="col-input">Mid Sem</th>
                  <th class="col-input">Final Sem</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="students.length === 0">
                  <td colspan="7" class="text-center">Belum ada siswa di kelas ini.</td>
                </tr>
                <tr v-for="(student, idx) in students" :key="student.siswa_id">
                  <td>{{ idx + 1 }}</td>
                  <td><strong>{{ student.name }}</strong></td>
                  <td v-for="(task, tIdx) in assignmentsList" :key="task.id">
                    <input type="number" step="0.01" min="0" max="100" v-model="student.assignments[tIdx].grade" class="score-input" :placeholder="student.assignments[tIdx].status === 'missing' ? 'Belum kumpul' : ''">
                  </td>
                  <td><input type="number" step="0.01" min="0" max="100" :value="student.nilai_tugas" class="score-input bg-gray-100" readonly title="Dihitung otomatis saat disimpan"></td>
                  <td><input type="number" step="0.01" min="0" max="100" v-model="student.nilai_quiz" class="score-input"></td>
                  <td><input type="number" step="0.01" min="0" max="100" v-model="student.nilai_harian" class="score-input"></td>
                  <td><input type="number" step="0.01" min="0" max="100" v-model="student.nilai_mid" class="score-input"></td>
                  <td><input type="number" step="0.01" min="0" max="100" v-model="student.nilai_final" class="score-input"></td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showModal = false">Batal</button>
            <button type="submit" class="primary-btn" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan Semua Nilai' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { authApi } from '@/api/auth';

const classes = ref([]);
const loadingClasses = ref(false);

const showModal = ref(false);
const activeClass = ref(null);
const loadingScores = ref(false);
const students = ref([]);
const assignmentsList = ref([]);
const saving = ref(false);

const loadClasses = async () => {
  loadingClasses.value = true;
  try {
    const res = await authApi.fetch('/guru/kelas');
    classes.value = res.classes ? res.classes.map(c => c.tingkat) : [];
  } catch (error) {
    console.error('Gagal memuat kelas:', error);
  } finally {
    loadingClasses.value = false;
  }
};

onMounted(() => {
  loadClasses();
});

const openInputModal = async (tingkat) => {
  activeClass.value = tingkat;
  showModal.value = true;
  loadingScores.value = true;
  
  try {
    const res = await authApi.fetch(`/guru/scores/${tingkat}`);
    students.value = res.students || [];
    assignmentsList.value = res.assignments_list || [];
  } catch (error) {
    console.error('Gagal memuat daftar siswa:', error);
    alert('Terjadi kesalahan saat mengambil daftar siswa.');
    showModal.value = false;
  } finally {
    loadingScores.value = false;
  }
};

const saveScores = async () => {
  saving.value = true;
  try {
    const payload = {
      scores: students.value.map(s => ({
        siswa_id: s.siswa_id,
        nilai_quiz: s.nilai_quiz !== '' && s.nilai_quiz !== null ? s.nilai_quiz : null,
        nilai_harian: s.nilai_harian !== '' && s.nilai_harian !== null ? s.nilai_harian : null,
        nilai_mid: s.nilai_mid !== '' && s.nilai_mid !== null ? s.nilai_mid : null,
        nilai_final: s.nilai_final !== '' && s.nilai_final !== null ? s.nilai_final : null,
        assignments: s.assignments ? s.assignments.map(a => ({
          assignment_id: a.assignment_id,
          grade: a.grade !== '' && a.grade !== null && a.grade !== undefined ? parseFloat(a.grade) : null
        })) : []
      }))
    };
    
    await authApi.fetch(`/guru/scores/${activeClass.value}`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    
    alert('Berhasil menyimpan semua nilai!');
    showModal.value = false;
  } catch (error) {
    console.error('Gagal menyimpan nilai:', error);
    alert('Terjadi kesalahan saat menyimpan nilai.');
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.page-grid {
  display: grid;
  gap: 1rem;
}
.panel {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.5rem;
}
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}
h2 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
}

.loading-state, .empty-state {
  padding: 2rem;
  text-align: center;
  color: #64748b;
  font-style: italic;
}

.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.class-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.class-card:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
  transform: translateY(-2px);
}

.class-card h3 {
  margin: 0 0 0.5rem 0;
  color: #0f172a;
  font-size: 1.25rem;
}

.class-card p {
  margin: 0;
  color: #64748b;
  font-size: 0.875rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  width: 95%;
  max-width: 1000px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 1.25rem;
  font-size: 1.25rem;
  color: #0f172a;
}

.table-wrap {
  overflow-y: auto;
  margin-bottom: 1rem;
}

.nilai-table {
  width: 100%;
  border-collapse: collapse;
}

.nilai-table th, .nilai-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}

.nilai-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
  position: sticky;
  top: 0;
  z-index: 10;
}

.col-input {
  width: 100px;
}

.score-input {
  width: 70px;
  padding: 0.4rem;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  text-align: center;
}

.score-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.bg-gray-100 {
  background-color: #f3f4f6;
  color: #6b7280;
  cursor: not-allowed;
}

.text-center {
  text-align: center;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel {
  background: white;
  border: 1px solid #cbd5e1;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  color: #475569;
}

.btn-cancel:hover {
  background: #f8fafc;
}

.primary-btn {
  background: #2563eb;
  color: white;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.primary-btn:hover {
  background: #1d4ed8;
}

.primary-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}
</style>
