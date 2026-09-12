<template>
  <div class="page-grid">
    <section class="profile-card">
      <div class="profile-head">
        <div class="avatar">{{ initials }}</div>
        <div>
          <h2 class="card-title">Profil Guru</h2>
          <p class="card-desc">Kelola data pribadi, keamanan akun, dan foto profil.</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state">Memuat profil...</div>
      <div v-else class="form-grid">
        <div class="field">
          <label>Nama lengkap</label>
          <input type="text" :value="profile?.name || ''" readonly />
        </div>
        <div class="field">
          <label>Email</label>
          <input type="text" :value="profile?.email || ''" readonly />
        </div>
        <div class="field">
          <label>Jenjang mengajar</label>
          <input type="text" :value="jenjangLabel" readonly />
        </div>
        <div v-if="profile?.jenjang_guru === 'smp' || profile?.jenjang_guru === 'smp_sma'" class="field">
          <label>Mata pelajaran (SMP)</label>
          <input type="text" :value="subjectSmpName || '—'" readonly />
        </div>
        <div v-if="profile?.jenjang_guru === 'sma' || profile?.jenjang_guru === 'smp_sma'" class="field">
          <label>Mata pelajaran (SMA)</label>
          <input type="text" :value="subjectSmaName || '—'" readonly />
        </div>
      </div>
      <div class="actions">
        <button type="button" class="primary-btn">Edit Profil</button>
        <button type="button" class="ghost-btn">Ganti Password</button>
        <button type="button" class="ghost-btn">Ubah Foto Profil</button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';

const profile = ref(null);
const loading = ref(true);
const subjectSmpName = ref('');
const subjectSmaName = ref('');
const initials = computed(() => {
  const n = (profile.value?.name || '').trim();
  if (!n) return 'G';
  const parts = n.split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return n.slice(0, 2).toUpperCase();
});

const jenjangLabel = computed(() => {
  const j = profile.value?.jenjang_guru;
  if (j === 'smp') return 'Guru SMP';
  if (j === 'sma') return 'Guru SMA';
  if (j === 'smp_sma') return 'Guru SMP & SMA';
  return '—';
});

onMounted(async () => {
  const API = (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, '');
  const token = localStorage.getItem('auth_token');
  if (!token) {
    loading.value = false;
    profile.value = {};
    return;
  }
  try {
    const res = await fetch(`${API}/user`, { headers: { Authorization: `Bearer ${token}` } });
    const data = await res.json();
    profile.value = data;
    const subsRes = await fetch(`${API}/subjects`);
    const subsData = await subsRes.json();
    const subjects = subsData.subjects || [];
    if (data.subject_smp_id) {
      const sub = subjects.find(x => x.id === data.subject_smp_id);
      subjectSmpName.value = sub ? `${sub.subject_name} (${sub.subject_code})` : '—';
    }
    if (data.subject_sma_id) {
      const sub = subjects.find(x => x.id === data.subject_sma_id);
      subjectSmaName.value = sub ? `${sub.subject_name} (${sub.subject_code})` : '—';
    }
  } catch (_) {
    profile.value = {};
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.page-grid {
  max-width: 760px;
}
.profile-card {
  padding: 1.25rem;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
}
.profile-head {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}
.avatar {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  letter-spacing: 0.03em;
}
.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}
.card-desc {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0 0 1.5rem 0;
  line-height: 1.5;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.9rem;
}
.field label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 0.35rem;
}
.field input {
  width: 100%;
  box-sizing: border-box;
  font-size: 0.92rem;
  color: #1e293b;
  padding: 0.6rem 0.75rem;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}
.actions {
  display: flex;
  gap: 0.55rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}
.primary-btn,
.ghost-btn {
  border-radius: 10px;
  font-size: 0.82rem;
  padding: 0.52rem 0.82rem;
  cursor: pointer;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
}
.loading-state {
  padding: 1.5rem;
  text-align: center;
  color: #64748b;
  font-size: 0.95rem;
}
@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
