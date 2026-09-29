<template>
  <div class="pendaftaran-form-page" :class="themeClass">
    <div class="header-section">
      <h2 class="page-title">Formulir Pendaftaran</h2>
      <p class="page-subtitle">Silakan lengkapi biodata, data pendidikan, dan data orang tua/wali Anda.</p>
    </div>

    <!-- Notifikasi Sukses -->
    <div v-if="successMsg" class="alert success-alert">
      <span class="icon">✅</span>
      <div class="content">
        <h4>Berhasil</h4>
        <p>{{ successMsg }}</p>
      </div>
    </div>

    <!-- Notifikasi Error (Global) -->
    <div v-if="errorMsg" class="alert error-alert">
      <span class="icon">⚠️</span>
      <div class="content">
        <h4>Terjadi Kesalahan</h4>
        <p>{{ errorMsg }}</p>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="form-container">
      
      <!-- DATA PRIBADI -->
      <PageCard :jenis-kelamin="jenisKelamin" class="form-card">
        <template #header>Data Pribadi</template>
        <div class="grid-form">
          <div class="form-group full-width">
            <label for="full_name">Nama Lengkap *</label>
            <input id="full_name" v-model="form.full_name" type="text" placeholder="Sesuai Akte Kelahiran" required />
          </div>
          <div class="form-group">
            <label for="gender">Jenis Kelamin *</label>
            <select id="gender" v-model="form.gender" required>
              <option value="laki-laki">Laki-laki</option>
              <option value="perempuan">Perempuan</option>
            </select>
          </div>
          <div class="form-group">
            <label for="tempat_lahir">Tempat Lahir *</label>
            <input id="tempat_lahir" v-model="form.tempat_lahir" type="text" placeholder="Kota Kelahiran" required />
          </div>
          <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir *</label>
            <input id="tanggal_lahir" v-model="form.tanggal_lahir" type="date" required />
          </div>
          <div class="form-group">
            <label for="agama">Agama *</label>
            <input id="agama" v-model="form.agama" type="text" required />
          </div>
          <div class="form-group">
            <label for="golongan_darah">Golongan Darah</label>
            <select id="golongan_darah" v-model="form.golongan_darah">
              <option value="">Pilih</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="AB">AB</option>
              <option value="O">O</option>
              <option value="Tidak Tahu">Tidak Tahu</option>
            </select>
          </div>
          <div class="form-group">
            <label for="kewarganegaraan">Kewarganegaraan *</label>
            <input id="kewarganegaraan" v-model="form.kewarganegaraan" type="text" required />
          </div>
          <div class="form-group">
            <label for="no_telp">Nomor Telepon Pribadi</label>
            <input id="no_telp" v-model="form.no_telp" type="tel" placeholder="08xxxxxxxxxx" />
          </div>
          <div class="form-group full-width">
            <label for="alamat">Alamat Lengkap *</label>
            <textarea id="alamat" v-model="form.alamat" rows="3" placeholder="Jalan, RT/RW, Desa/Kelurahan" required></textarea>
          </div>
        </div>
      </PageCard>

      <!-- DATA PENDIDIKAN -->
      <PageCard :jenis-kelamin="jenisKelamin" class="form-card">
        <template #header>Data Pendidikan</template>
        <div class="grid-form">
          <div class="form-group">
            <label for="kelas_yang_didaftar">Kelas Pendaftaran *</label>
            <select id="kelas_yang_didaftar" v-model="form.kelas_yang_didaftar" required>
              <option value="" disabled>Pilih Kelas</option>
              <option value="7">Kelas 7 (SMP)</option>
              <option value="8">Kelas 8 (SMP)</option>
              <option value="9">Kelas 9 (SMP)</option>
              <option value="10">Kelas 10 (SMA)</option>
              <option value="11">Kelas 11 (SMA)</option>
              <option value="12">Kelas 12 (SMA)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_transfer_student" />
              <span>Siswa Pindahan</span>
            </label>
          </div>
          <div class="form-group full-width">
            <label for="previous_school_name">Asal Sekolah</label>
            <input id="previous_school_name" v-model="form.previous_school_name" type="text" placeholder="Nama SD/SMP asal" />
          </div>
        </div>
      </PageCard>

      <!-- DATA ORANG TUA/WALI -->
      <PageCard :jenis-kelamin="jenisKelamin" class="form-card">
        <template #header>Data Orang Tua / Wali</template>
        <div class="grid-form">
          <div class="form-group full-width">
            <label for="no_telp_ortu">Nomor Telepon Utama Orang Tua / Wali * (Gunakan awalan 08 atau 62)</label>
            <input id="no_telp_ortu" v-model="form.no_telp_ortu" type="tel" pattern="^(08|62)[0-9]{8,13}$" placeholder="Contoh: 08123456789 atau 628123456789" required />
            <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">Pastikan nomor aktif dan dapat dihubungi, diawali dengan 08 atau 62.</small>
          </div>

          <h4 class="section-title full-width" style="margin-top: 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #1e293b;">Data Ayah</h4>
          <div class="form-group">
            <label for="nama_ayah">Nama Ayah *</label>
            <input id="nama_ayah" v-model="form.nama_ayah" type="text" required />
          </div>
          <div class="form-group">
            <label for="pendidikan_ayah">Pendidikan Ayah</label>
            <input id="pendidikan_ayah" v-model="form.pendidikan_ayah" type="text" />
          </div>
          <div class="form-group">
            <label for="pekerjaan_ayah">Pekerjaan Ayah *</label>
            <input id="pekerjaan_ayah" v-model="form.pekerjaan_ayah" type="text" required />
          </div>
          <div class="form-group">
            <label for="penghasilan_ayah">Penghasilan Ayah</label>
            <input id="penghasilan_ayah" v-model="form.penghasilan_ayah" type="text" placeholder="Rp." />
          </div>
          <div class="form-group">
            <label for="agama_ayah">Agama Ayah</label>
            <input id="agama_ayah" v-model="form.agama_ayah" type="text" />
          </div>
          <div class="form-group full-width">
            <label for="kewarganegaraan_ayah">Kewarganegaraan Ayah</label>
            <input id="kewarganegaraan_ayah" v-model="form.kewarganegaraan_ayah" type="text" />
          </div>

          <h4 class="section-title full-width" style="margin-top: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; color: #1e293b;">Data Ibu</h4>
          <div class="form-group">
            <label for="nama_ibu">Nama Ibu *</label>
            <input id="nama_ibu" v-model="form.nama_ibu" type="text" required />
          </div>
          <div class="form-group">
            <label for="pendidikan_ibu">Pendidikan Ibu</label>
            <input id="pendidikan_ibu" v-model="form.pendidikan_ibu" type="text" />
          </div>
          <div class="form-group">
            <label for="pekerjaan_ibu">Pekerjaan Ibu *</label>
            <input id="pekerjaan_ibu" v-model="form.pekerjaan_ibu" type="text" required />
          </div>
          <div class="form-group">
            <label for="penghasilan_ibu">Penghasilan Ibu</label>
            <input id="penghasilan_ibu" v-model="form.penghasilan_ibu" type="text" placeholder="Rp." />
          </div>
          <div class="form-group">
            <label for="agama_ibu">Agama Ibu</label>
            <input id="agama_ibu" v-model="form.agama_ibu" type="text" />
          </div>
          <div class="form-group full-width">
            <label for="kewarganegaraan_ibu">Kewarganegaraan Ibu</label>
            <input id="kewarganegaraan_ibu" v-model="form.kewarganegaraan_ibu" type="text" />
          </div>
        </div>
      </PageCard>

      <!-- SUBMIT ACTION -->
      <div class="form-actions">
        <button type="submit" class="btn-submit" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          {{ loading ? 'Menyimpan Data...' : 'Simpan Pendaftaran' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { pendaftaranApi } from '@/api/pendaftaran';
import PageCard from '@/components/PageCard.vue';

const props = defineProps({
  jenisKelamin: { type: String, default: 'laki-laki' },
});

const themeClass = computed(() => 'theme-' + props.jenisKelamin);
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const form = reactive({
  full_name: '',
  gender: props.jenisKelamin,
  tempat_lahir: '',
  tanggal_lahir: '',
  alamat: '',
  is_transfer_student: false,
  previous_school_name: '',
  kelas_yang_didaftar: '',

  agama: '',
  golongan_darah: '',
  kewarganegaraan: 'WNI',
  no_telp: '',

  nama_ayah: '',
  pendidikan_ayah: '',
  pekerjaan_ayah: '',
  penghasilan_ayah: '',
  no_telp_ayah: '',
  agama_ayah: '',
  kewarganegaraan_ayah: 'WNI',

  nama_ibu: '',
  pendidikan_ibu: '',
  pekerjaan_ibu: '',
  penghasilan_ibu: '',
  no_telp_ibu: '',
  agama_ibu: '',
  kewarganegaraan_ibu: 'WNI',
  
  no_telp_ortu: '',
});

onMounted(() => {
  // Pre-fill from user context if available
  if (authStore.user) {
    form.full_name = authStore.user.name || form.full_name;
    form.gender = authStore.user.jenis_kelamin || form.gender;
  }
});

async function handleSubmit() {
  errorMsg.value = '';
  successMsg.value = '';
  
  // Validasi Frontend Tambahan
  if (!form.full_name || !form.tempat_lahir || !form.tanggal_lahir || !form.alamat || !form.nama_ayah || !form.nama_ibu || !form.kelas_yang_didaftar || !form.agama || !form.kewarganegaraan || !form.no_telp_ortu) {
    errorMsg.value = 'Silakan lengkapi semua field yang bertanda bintang (*).';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  loading.value = true;
  try {
    const res = await pendaftaranApi.submitForm(form);
    successMsg.value = res.message || 'Data pendaftaran berhasil disimpan.';
    
    // Langsung redirect ke halaman status
    router.push(`/siswa/${props.jenisKelamin}/pendaftaran/status`);
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (e) {
    errorMsg.value = e.message || 'Gagal menyimpan pendaftaran. Periksa koneksi Anda.';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.pendaftaran-form-page {
  padding-bottom: 3rem;
  max-width: 800px;
  margin: 0 auto;
}

.header-section {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
}

.alert {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.alert h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
}

.alert p {
  margin: 0;
  font-size: 0.9rem;
}

.success-alert {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

.error-alert {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.grid-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.4rem;
}

.form-group input:not([type="checkbox"]),
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: all 0.2s;
  background: #fff;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
}

.theme-perempuan .form-group input:focus,
.theme-perempuan .form-group select:focus,
.theme-perempuan .form-group textarea:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  margin-top: 1.5rem;
}

.checkbox-label input[type="checkbox"] {
  width: 1.2rem;
  height: 1.2rem;
  cursor: pointer;
  accent-color: var(--primary);
}

.theme-perempuan .checkbox-label input[type="checkbox"] {
  accent-color: #7c3aed;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
}

.btn-submit {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.85rem 2rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.theme-laki-laki .btn-submit {
  background: #0f766e;
}

.theme-perempuan .btn-submit {
  background: #7c3aed;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .grid-form {
    grid-template-columns: 1fr;
  }
}
</style>
