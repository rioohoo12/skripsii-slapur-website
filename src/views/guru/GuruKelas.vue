<template>
  <div class="page-grid">
    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Data Kelas</h2>
          <p>Daftar kelas berdasarkan siswa yang sudah menyelesaikan pendaftaran dan pembayaran 60%.</p>
        </div>
      </div>
      <div v-if="loadingKelas" class="placeholder">Memuat data kelas...</div>
      <div v-else-if="!classes.length" class="placeholder">
        Belum ada siswa yang terdaftar pada kelas Anda. Pastikan siswa sudah mengisi pendaftaran dan pembayaran 60%.
      </div>
      <div v-else class="class-grid">
        <article
          v-for="kelas in classes"
          :key="kelas.tingkat"
          class="class-card"
          :class="{ active: selectedClass && selectedClass.tingkat === kelas.tingkat }"
          @click="selectClass(kelas)"
        >
          <p class="name">{{ kelas.name }}</p>
          <p class="meta">Tingkat {{ kelas.tingkat }}</p>
          <div class="chips">
            <span>{{ kelas.students_count }} siswa</span>
          </div>
        </article>
      </div>
    </section>

    <section class="panel">
      <h3 v-if="selectedClass">
        Daftar Siswa - {{ selectedClass.name }}
      </h3>
      <h3 v-else>Daftar Siswa</h3>

      <div v-if="loadingSiswa" class="placeholder">Memuat daftar siswa...</div>
      <div v-else-if="selectedClass && !students.length" class="placeholder">
        Belum ada siswa pada kelas ini.
      </div>
      <div v-else-if="!selectedClass" class="placeholder">
        Pilih salah satu kelas di panel sebelah kiri untuk melihat daftar siswa.
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Jenis Kelamin</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(student, idx) in students" :key="student.id">
              <td>{{ idx + 1 }}</td>
              <td>{{ student.nama }}</td>
              <td>{{ student.email || '-' }}</td>
              <td>{{ formatGender(student.jenis_kelamin) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { guruApi } from '@/api/guru';

const classes = ref([]);
const selectedClass = ref(null);
const students = ref([]);
const loadingKelas = ref(false);
const loadingSiswa = ref(false);

function formatGender(value) {
  if (value === 'laki-laki') return 'Laki-laki';
  if (value === 'perempuan') return 'Perempuan';
  return '-';
}

async function loadKelas() {
  loadingKelas.value = true;
  try {
    const res = await guruApi.getKelas();
    classes.value = res.classes || [];
    if (classes.value.length) {
      await selectClass(classes.value[0]);
    }
  } catch (e) {
    console.error(e);
  } finally {
    loadingKelas.value = false;
  }
}

async function selectClass(kelas) {
  selectedClass.value = kelas;
  students.value = [];
  loadingSiswa.value = true;
  try {
    const res = await guruApi.getKelasDetail(kelas.tingkat);
    students.value = res.students || [];
  } catch (e) {
    console.error(e);
  } finally {
    loadingSiswa.value = false;
  }
}

onMounted(() => {
  loadKelas();
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
  padding: 1rem;
}
.panel h2,
.panel h3 {
  margin: 0;
  color: #1e293b;
}
.panel p {
  margin: 0.3rem 0 0;
  color: #64748b;
  font-size: 0.9rem;
}
.placeholder {
  color: #94a3b8;
  font-size: 0.9rem;
}
.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.9rem;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 10px;
  padding: 0.55rem 0.9rem;
  font-size: 0.85rem;
  cursor: pointer;
}
.class-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.7rem;
}
.class-card {
  border: 1px solid #dbeafe;
  background: #f8fbff;
  border-radius: 12px;
  padding: 0.8rem;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.class-card.active {
  border-color: #2563eb;
  box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.4);
  background: #eff6ff;
}
.class-card .name {
  margin: 0;
  font-weight: 700;
  color: #0f172a;
}
.class-card .meta {
  margin: 0.2rem 0 0.4rem;
}
.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}
.chips span {
  background: #e2e8f0;
  color: #334155;
  border-radius: 999px;
  padding: 0.25rem 0.55rem;
  font-size: 0.75rem;
}
.table-wrap {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.65rem;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.88rem;
}
th {
  color: #475569;
  font-weight: 700;
}
.status {
  background: #dcfce7;
  color: #166534;
  border-radius: 999px;
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
}
@media (max-width: 980px) {
  .class-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 640px) {
  .panel-head {
    flex-direction: column;
    align-items: flex-start;
  }
  .class-grid {
    grid-template-columns: 1fr;
  }
}
</style>
