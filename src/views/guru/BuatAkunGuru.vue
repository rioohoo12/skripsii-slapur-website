<template>
  <div class="login-page login-guru">
    <div class="login-card card-wide">
      <img src="/slapur-logo.png" alt="SLAPUR" class="login-logo" />
      <h1 class="login-title">Buat Akun Guru</h1>
      <p class="login-subtitle">Daftar akun guru — pilih jenjang dan mata pelajaran</p>

      <form class="login-form" @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="nama">Nama lengkap</label>
          <input
            id="nama"
            v-model="form.nama"
            type="text"
            placeholder="Masukkan nama lengkap"
            required
          />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Masukkan email"
            required
          />
        </div>

        <div class="form-group">
          <label for="jenjang">Saya guru</label>
          <select id="jenjang" v-model="form.jenjangGuru" required @change="onJenjangChange">
            <option value="" disabled>Pilih jenjang</option>
            <option value="smp">Guru SMP</option>
            <option value="sma">Guru SMA</option>
            <option value="smp_sma">Guru SMP & SMA (kedua-duanya)</option>
          </select>
        </div>

        <div v-if="showSubjectSmp" class="form-group">
          <label for="subjectSmp">Mata pelajaran (SMP)</label>
          <select id="subjectSmp" v-model="form.subjectSmpId">
            <option value="" disabled>Pilih mata pelajaran SMP</option>
            <option v-for="s in subjectsSmp" :key="s.id" :value="s.id">
              {{ s.subject_name }} ({{ s.subject_code }})
            </option>
          </select>
        </div>

        <div v-if="showSubjectSma" class="form-group">
          <label for="subjectSma">Mata pelajaran (SMA)</label>
          <select id="subjectSma" v-model="form.subjectSmaId">
            <option value="" disabled>Pilih mata pelajaran SMA</option>
            <option v-for="s in subjectsSma" :key="s.id" :value="s.id">
              {{ s.subject_name }} ({{ s.subject_code }})
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Min. 6 karakter"
            required
            minlength="6"
          />
        </div>
        <div class="form-group">
          <label for="confirmPassword">Konfirmasi password</label>
          <input
            id="confirmPassword"
            v-model="form.confirmPassword"
            type="password"
            placeholder="Ulangi password"
            required
            minlength="6"
          />
        </div>
        <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>
        <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        <button type="submit" class="login-btn guru" :disabled="loading">
          <span v-if="loading" class="btn-loading">
            <span class="spinner"></span>
            Memproses...
          </span>
          <span v-else>Daftar</span>
        </button>
      </form>
      <p class="login-footer">
        Sudah punya akun?
        <router-link to="/guru" class="link">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { authApi } from '@/api/auth';

const router = useRouter();
const API_BASE = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '');

const form = reactive({
  nama: '',
  email: '',
  password: '',
  confirmPassword: '',
  jenjangGuru: '',
  subjectSmpId: '',
  subjectSmaId: '',
});
const loading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');
const subjectsSmp = ref([]);
const subjectsSma = ref([]);

const showSubjectSmp = computed(() =>
  form.jenjangGuru === 'smp' || form.jenjangGuru === 'smp_sma');
const showSubjectSma = computed(() =>
  form.jenjangGuru === 'sma' || form.jenjangGuru === 'smp_sma');

function onJenjangChange() {
  form.subjectSmpId = '';
  form.subjectSmaId = '';
}

async function fetchSubjects(jenjang) {
  try {
    const res = await fetch(`${API_BASE}/subjects?jenjang=${jenjang}`);
    const data = await res.json();
    return data.subjects || [];
  } catch {
    return [];
  }
}

onMounted(async () => {
  [subjectsSmp.value, subjectsSma.value] = await Promise.all([
    fetchSubjects('smp'),
    fetchSubjects('sma'),
  ]);
});

async function handleSubmit() {
  errorMsg.value = '';
  successMsg.value = '';
  if (form.password !== form.confirmPassword) {
    errorMsg.value = 'Konfirmasi password tidak sama.';
    return;
  }
  if (form.password.length < 6) {
    errorMsg.value = 'Password minimal 6 karakter.';
    return;
  }
  if (!form.jenjangGuru) {
    errorMsg.value = 'Pilih jenjang (SMP / SMA / Kedua-duanya).';
    return;
  }
  if (showSubjectSmp.value && !form.subjectSmpId) {
    errorMsg.value = 'Pilih mata pelajaran SMP.';
    return;
  }
  if (showSubjectSma.value && !form.subjectSmaId) {
    errorMsg.value = 'Pilih mata pelajaran SMA.';
    return;
  }
  loading.value = true;
  try {
    await authApi.registerGuru({
      nama: form.nama.trim(),
      email: form.email.trim(),
      password: form.password,
      confirmPassword: form.confirmPassword,
      jenjangGuru: form.jenjangGuru,
      subjectSmpId: showSubjectSmp.value ? form.subjectSmpId : undefined,
      subjectSmaId: showSubjectSma.value ? form.subjectSmaId : undefined,
    });
    successMsg.value = 'Akun berhasil dibuat. Silakan login.';
    setTimeout(() => router.push('/guru'), 1500);
  } catch (e) {
    errorMsg.value = e?.message || 'Gagal mendaftar. Coba lagi.';
    if (e?.errors?.email) {
      errorMsg.value = Array.isArray(e.errors.email) ? e.errors.email[0] : e.errors.email;
    }
    if (e?.errors?.subject_smp_id) {
      errorMsg.value = Array.isArray(e.errors.subject_smp_id) ? e.errors.subject_smp_id[0] : e.errors.subject_smp_id;
    }
    if (e?.errors?.subject_sma_id) {
      errorMsg.value = Array.isArray(e.errors.subject_sma_id) ? e.errors.subject_sma_id[0] : e.errors.subject_sma_id;
    }
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-guru .login-title { color: #5b21b6; }
.login-guru .login-btn.guru { background: #5b21b6; }
.login-guru .login-btn.guru:hover:not(:disabled) { background: #7c3aed; }
.login-guru .login-footer .link { color: #5b21b6; font-weight: 600; text-decoration: none; }
.login-guru .login-footer .link:hover { text-decoration: underline; }
.login-guru .form-group input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
.login-guru .form-group select:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1); }
.error-msg { font-size: 0.875rem; color: #dc2626; margin-bottom: 1rem; }
.success-msg { font-size: 0.875rem; color: #059669; margin-bottom: 1rem; }
.card-wide { max-width: 420px; }
</style>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #e0e7ff 100%);
  padding: 1rem;
}
.login-card {
  width: 100%;
  max-width: 400px;
  padding: 2.5rem;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  text-align: center;
}
.login-logo { width: 80px; height: 80px; margin: 0 auto 1.25rem; display: block; }
.login-subtitle { font-size: 0.95rem; color: #64748b; margin-bottom: 1.25rem; }
.login-form { text-align: left; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  font-family: inherit;
}
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  font-family: inherit;
  background-color: #fff;
}
.login-btn {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}
.login-btn:disabled { opacity: 0.85; cursor: wait; }
.btn-loading { display: inline-flex; align-items: center; gap: 0.5rem; }
.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.login-footer { margin-top: 1.25rem; font-size: 0.9rem; color: #64748b; }
</style>
