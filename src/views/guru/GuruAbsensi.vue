<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Absensi Siswa</h2>
          <p>Pilih kelas untuk mencatat absensi hari ini.</p>
        </div>
      </div>
      
      <div class="class-selector-tabs">
        <button 
          v-for="cls in availableClasses" 
          :key="cls.tingkat" 
          type="button"
          :class="['class-tab', { active: selectedTingkat === cls.tingkat }]"
          @click="selectClass(cls.tingkat)"
          :disabled="loading"
        >
          {{ cls.name }}
        </button>
        
        <div class="date-picker">
          <label>Tanggal:</label>
          <input type="date" v-model="currentDate" @change="loadStudents" :disabled="loading" />
        </div>
      </div>

      <div v-if="loading" class="loading-state">Memuat data...</div>

      <div v-else-if="selectedTingkat && students.length === 0" class="empty-state">
        Belum ada siswa yang terdaftar atau memenuhi syarat di {{ selectedClassName }}.
      </div>

      <div v-else-if="selectedTingkat && students.length > 0">
        <div class="head" style="margin-top: 1.5rem;">
          <h3>Daftar Siswa - {{ selectedClassName }}</h3>
          <button class="primary-btn" type="button" @click="saveAttendance" :disabled="saving">
            {{ saving ? 'Menyimpan...' : 'Simpan Absensi' }}
          </button>
        </div>
        
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Status Kehadiran</th>
                <th>Keterangan Tambahan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(student, index) in students" :key="student.id">
                <td>{{ index + 1 }}</td>
                <td>{{ student.nama }}</td>
                <td>
                  <div class="radio-group">
                    <label class="radio-label hadir">
                      <input type="radio" v-model="student.status" value="Hadir"> Hadir
                    </label>
                    <label class="radio-label izin">
                      <input type="radio" v-model="student.status" value="Izin"> Izin
                    </label>
                    <label class="radio-label sakit">
                      <input type="radio" v-model="student.status" value="Sakit"> Sakit
                    </label>
                    <label class="radio-label alpha">
                      <input type="radio" v-model="student.status" value="Alpha"> Alpha
                    </label>
                  </div>
                </td>
                <td>
                  <input type="text" v-model="student.remarks" placeholder="Catatan (opsional)" class="input-remarks" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { authApi } from '@/api/auth';

const user = computed(() => {
  try { return JSON.parse(localStorage.getItem('user') || '{}'); } catch { return {}; }
});

const availableClasses = ref([]);
const selectedTingkat = ref('');
const currentDate = ref(new Date().toISOString().split('T')[0]);
const students = ref([]);
const loading = ref(false);
const saving = ref(false);

const selectedClassName = computed(() => {
  const cls = availableClasses.value.find(c => c.tingkat === selectedTingkat.value);
  return cls ? cls.name : 'Kelas';
});

const loadClasses = () => {
  const j = user.value.jenjang_guru || 'smp_sma'; // Fallback if missing
  const classes = [];
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

const selectClass = (tingkat) => {
  selectedTingkat.value = tingkat;
  loadStudents();
};

const loadStudents = async () => {
  if (!selectedTingkat.value) {
    students.value = [];
    return;
  }
  
  loading.value = true;
  try {
    // Ambil data siswa di kelas tersebut dari backend
    const resSiswa = await authApi.fetch(`/guru/kelas/${selectedTingkat.value}`);
    let dataSiswa = resSiswa.students || [];
    
    // Ambil data absensi yang mungkin sudah disimpan hari ini
    const resAbsen = await authApi.fetch(`/guru/absensi?tingkat=${selectedTingkat.value}&date=${currentDate.value}`);
    const savedAttendances = resAbsen.attendances || [];
    
    // Gabungkan data
    dataSiswa = dataSiswa.map(s => {
      const existing = savedAttendances.find(a => a.siswa_id === s.id);
      return {
        ...s,
        status: existing ? existing.status : 'Hadir', // Default Hadir
        remarks: existing ? existing.remarks : ''
      };
    });
    
    students.value = dataSiswa;
  } catch (error) {
    console.error("Gagal memuat data siswa:", error);
    if (error.response) {
       console.error("Response data:", error.response.data);
    }
    alert("Gagal memuat data siswa: " + (error.message || error));
  } finally {
    loading.value = false;
  }
};

const saveAttendance = async () => {
  saving.value = true;
  const subj = user.value.jenjang_guru === 'sma' ? user.value.subject_sma_name : user.value.subject_smp_name;
  const payload = {
    tingkat: selectedTingkat.value,
    date: currentDate.value,
    subject_name: subj || 'Mata Pelajaran',
    attendances: students.value.map(s => ({
      siswa_id: s.id,
      status: s.status,
      remarks: s.remarks
    }))
  };
  
  try {
    const res = await authApi.fetch('/guru/absensi', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    alert(res.message || 'Absensi berhasil disimpan!');
  } catch (error) {
    console.error("Gagal menyimpan", error);
    alert("Gagal menyimpan absensi.");
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadClasses();
});
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
  padding: 1.25rem;
}
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}
h2, h3 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
}
.class-selector-tabs {
  margin-bottom: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  background: #f8fafc;
  padding: 1rem;
  border-radius: 10px;
}
.class-tab {
  background: #fff;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.class-tab:hover:not(:disabled) {
  border-color: #94a3b8;
  background: #f1f5f9;
}
.class-tab.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}
.class-tab:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.date-picker {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.date-picker input {
  padding: 0.45rem 0.6rem;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 10px;
  padding: 0.65rem 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}
.primary-btn:hover {
  opacity: 0.9;
}
.primary-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}
.table-wrap {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  text-align: left;
  padding: 0.85rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.9rem;
}
th {
  color: #475569;
  font-weight: 600;
  background: #f8fafc;
}
.radio-group {
  display: flex;
  gap: 0.75rem;
}
.radio-label {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.85rem;
  cursor: pointer;
}
.radio-label input {
  cursor: pointer;
}
.radio-label.hadir { color: #166534; }
.radio-label.izin { color: #92400e; }
.radio-label.sakit { color: #1d4ed8; }
.radio-label.alpha { color: #991b1b; }
.input-remarks {
  width: 100%;
  padding: 0.4rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.85rem;
}
.loading-state, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #64748b;
  background: #f8fafc;
  border-radius: 10px;
}
</style>
