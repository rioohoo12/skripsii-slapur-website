<template>
  <div class="biodata-page">
    <!-- Bar aksi singkat (opsional) -->
    <div class="action-bar">
      <span class="action-label">Biodata & Data Keluarga</span>
    </div>

    <div class="biodata-cards">
      <!-- Card 1: Biodata Pribadi -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-biodata">
        <template #header>Biodata</template>
        <div class="profile-section">
          <div class="avatar" :class="themeClass">
            <span class="avatar-inner">{{ initialName }}</span>
          </div>
          <div class="profile-info">
            <h3 class="profile-name">{{ biodata.nama }} ({{ biodata.nis || '—' }})</h3>
          </div>
        </div>
        <dl class="data-list">
          <div class="data-row">
            <dt>Nomor Induk / NIS</dt>
            <dd>{{ biodata.nis || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Kelas / Program</dt>
            <dd>{{ biodata.kelas || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Status</dt>
            <dd>{{ biodata.status || 'Siswa Aktif' }}</dd>
          </div>
          <div class="data-row">
            <dt>Jenis Kelamin</dt>
            <dd>{{ biodata.jenisKelamin === 'perempuan' ? 'Perempuan' : 'Laki-laki' }}</dd>
          </div>
          <div class="data-row">
            <dt>Tempat, Tanggal Lahir</dt>
            <dd>{{ biodata.tempatLahir || '—' }}, {{ biodata.tanggalLahir || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>E-mail</dt>
            <dd>{{ biodata.email || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Nomor Telepon</dt>
            <dd>{{ biodata.telepon || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Agama</dt>
            <dd>{{ biodata.agama || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Golongan Darah</dt>
            <dd>{{ biodata.golonganDarah || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Kewarganegaraan</dt>
            <dd>{{ biodata.kewarganegaraan || 'WNI' }}</dd>
          </div>
          <div class="data-row">
            <dt>Alamat</dt>
            <dd>{{ biodata.alamat || '—' }}</dd>
          </div>
          <div class="data-row">
            <dt>Kota / Kabupaten</dt>
            <dd>{{ biodata.kota || '—' }}</dd>
          </div>
        </dl>
      </PageCard>

      <!-- Card 2: Orang Tua -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-orang-tua">
        <template #header>
          <span>Orang Tua</span>
          <button type="button" class="toggle-header" :aria-expanded="orangTuaOpen" @click="orangTuaOpen = !orangTuaOpen">
            <span class="arrow" :class="{ open: orangTuaOpen }">▼</span>
          </button>
        </template>
        <div v-show="orangTuaOpen" class="parent-sections">
          <section class="parent-block">
            <h4 class="parent-title">Ayah</h4>
            <dl class="data-list compact">
              <div class="data-row"><dt>Nama</dt><dd>{{ orangTua.ayah.nama || '—' }}</dd></div>
              <div class="data-row"><dt>Pendidikan</dt><dd>{{ orangTua.ayah.pendidikan || '—' }}</dd></div>
              <div class="data-row"><dt>Pekerjaan</dt><dd>{{ orangTua.ayah.pekerjaan || '—' }}</dd></div>
              <div class="data-row"><dt>Penghasilan</dt><dd>{{ orangTua.ayah.penghasilan || '—' }}</dd></div>
              <div class="data-row"><dt>Nomor Telepon</dt><dd>{{ orangTua.ayah.telepon || '—' }}</dd></div>
              <div class="data-row"><dt>Alamat</dt><dd>{{ orangTua.ayah.alamat || '—' }}</dd></div>
              <div class="data-row"><dt>Kewarganegaraan</dt><dd>{{ orangTua.ayah.kewarganegaraan || 'WNI' }}</dd></div>
            </dl>
          </section>
          <section class="parent-block">
            <h4 class="parent-title">Ibu</h4>
            <dl class="data-list compact">
              <div class="data-row"><dt>Nama</dt><dd>{{ orangTua.ibu.nama || '—' }}</dd></div>
              <div class="data-row"><dt>Pendidikan</dt><dd>{{ orangTua.ibu.pendidikan || '—' }}</dd></div>
              <div class="data-row"><dt>Pekerjaan</dt><dd>{{ orangTua.ibu.pekerjaan || '—' }}</dd></div>
              <div class="data-row"><dt>Penghasilan</dt><dd>{{ orangTua.ibu.penghasilan || '—' }}</dd></div>
              <div class="data-row"><dt>Nomor Telepon</dt><dd>{{ orangTua.ibu.telepon || '—' }}</dd></div>
              <div class="data-row"><dt>Alamat</dt><dd>{{ orangTua.ibu.alamat || '—' }}</dd></div>
              <div class="data-row"><dt>Kewarganegaraan</dt><dd>{{ orangTua.ibu.kewarganegaraan || 'WNI' }}</dd></div>
            </dl>
          </section>
        </div>
      </PageCard>

      <!-- Card 3: Wali / Sponsor -->
      <PageCard :jenis-kelamin="jenisKelamin" class="card-wali">
        <template #header>
          <span>Wali / Sponsor</span>
          <button type="button" class="toggle-header" :aria-expanded="waliOpen" @click="waliOpen = !waliOpen">
            <span class="arrow" :class="{ open: waliOpen }">▼</span>
          </button>
        </template>
        <div v-show="waliOpen" class="wali-content">
          <dl class="data-list compact">
            <div class="data-row"><dt>Nama</dt><dd>{{ wali.nama || '—' }}</dd></div>
            <div class="data-row"><dt>Pendidikan</dt><dd>{{ wali.pendidikan || '—' }}</dd></div>
            <div class="data-row"><dt>Pekerjaan</dt><dd>{{ wali.pekerjaan || '—' }}</dd></div>
            <div class="data-row"><dt>Penghasilan</dt><dd>{{ wali.penghasilan || '—' }}</dd></div>
            <div class="data-row"><dt>Nomor Telepon</dt><dd>{{ wali.telepon || '—' }}</dd></div>
            <div class="data-row"><dt>Alamat</dt><dd>{{ wali.alamat || '—' }}</dd></div>
            <div class="data-row"><dt>Agama</dt><dd>{{ wali.agama || '—' }}</dd></div>
            <div class="data-row"><dt>Kewarganegaraan</dt><dd>{{ wali.kewarganegaraan || 'WNI' }}</dd></div>
          </dl>
        </div>
      </PageCard>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const orangTuaOpen = ref(true);
const waliOpen = ref(true);

const biodata = ref({
  nama: '',
  nis: '',
  email: '',
  jenisKelamin: props.jenisKelamin,
  kelas: '',
  status: '',
  tempatLahir: '',
  tanggalLahir: '',
  telepon: '',
  agama: '',
  golonganDarah: '',
  kewarganegaraan: 'WNI',
  alamat: '',
  kota: '',
});

const orangTua = ref({
  ayah: {
    nama: '',
    pendidikan: '',
    pekerjaan: '',
    penghasilan: '',
    telepon: '',
    alamat: '',
    kewarganegaraan: 'WNI',
  },
  ibu: {
    nama: '',
    pendidikan: '',
    pekerjaan: '',
    penghasilan: '',
    telepon: '',
    alamat: '',
    kewarganegaraan: 'WNI',
  },
});

const wali = ref({
  nama: '',
  pendidikan: '',
  pekerjaan: '',
  penghasilan: '',
  telepon: '',
  alamat: '',
  agama: '',
  kewarganegaraan: 'WNI',
});

const initialName = computed(() => {
  const n = biodata.value.nama || 'S';
  const parts = n.trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  return n.slice(0, 2).toUpperCase();
});

onMounted(() => {
  try {
    const user = localStorage.getItem('user');
    if (user) {
      const u = JSON.parse(user);
      biodata.value.nama = u.name || u.nama || '';
      biodata.value.email = u.email || '';
      biodata.value.jenisKelamin = u.jenis_kelamin || props.jenisKelamin;
    }
  } catch (_) {}
});
</script>

<style scoped>
.biodata-page {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.action-bar {
  padding: 0.6rem 0;
}

.action-label {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}

.biodata-cards {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 1100px) {
  .biodata-cards {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 700px) {
  .biodata-cards {
    grid-template-columns: 1fr;
  }
}

.card-biodata :deep(.page-card-header),
.card-orang-tua :deep(.page-card-header),
.card-wali :deep(.page-card-header) {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.toggle-header {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: inherit;
}

.toggle-header .arrow {
  display: inline-block;
  font-size: 0.7rem;
  transition: transform 0.2s;
}

.toggle-header .arrow.open {
  transform: rotate(180deg);
}

.profile-section {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar.theme-laki-laki {
  background: linear-gradient(135deg, #99f6e4 0%, #5eead4 100%);
}

.avatar.theme-perempuan {
  background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
}

.avatar-inner {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.profile-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.data-list {
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.data-list.compact {
  gap: 0.5rem;
}

.data-row {
  display: grid;
  grid-template-columns: 160px 1fr;
  gap: 0.75rem;
  align-items: baseline;
}

.data-row dt {
  margin: 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

.data-row dd {
  margin: 0;
  font-size: 0.9rem;
  color: #1e293b;
}

.parent-sections {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.parent-block {
  padding: 1rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.parent-block:last-child {
  border-bottom: none;
}

.parent-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #334155;
  margin: 0 0 0.75rem 0;
}

.wali-content {
  padding-top: 0.25rem;
}
</style>
