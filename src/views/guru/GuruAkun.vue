<template>
  <div class="page-container">
    <!-- Header Banner -->
    <div class="profile-banner">
      <div class="banner-content">
        <div class="avatar-wrapper">
          <div class="avatar">{{ initials }}</div>
          <button class="edit-avatar-btn" title="Ubah Foto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </button>
        </div>
        <div class="banner-text">
          <h2 class="profile-name">{{ profile?.name || 'Memuat...' }}</h2>
          <p class="profile-role">{{ jenjangLabel }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="profile-content">
      <!-- Info Card -->
      <section class="info-card">
        <div class="card-header">
          <h3>Informasi Pribadi</h3>
          <p>Detail profil dan data mengajar Anda</p>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          Memuat data profil...
        </div>
        
        <div v-else class="info-grid">
          <div class="info-group">
            <span class="info-label">Nama Lengkap</span>
            <div class="info-value">
              <span class="icon">👤</span>
              <input type="text" :value="profile?.name || ''" readonly />
            </div>
          </div>
          
          <div class="info-group">
            <span class="info-label">Alamat Email</span>
            <div class="info-value">
              <span class="icon">✉️</span>
              <input type="email" :value="profile?.email || ''" readonly />
            </div>
          </div>

          <div class="info-group">
            <span class="info-label">Jenjang Mengajar</span>
            <div class="info-value">
              <span class="icon">🏫</span>
              <input type="text" :value="jenjangLabel" readonly />
            </div>
          </div>
          
          <div class="info-group">
            <span class="info-label">Status Akun</span>
            <div class="info-value">
              <span class="icon">✅</span>
              <div class="status-badge">Aktif</div>
            </div>
          </div>

          <!-- Hanya Tampilkan Jika Guru SMP / SMP_SMA -->
          <div v-if="profile?.jenjang_guru === 'smp' || profile?.jenjang_guru === 'smp_sma'" class="info-group full-width">
            <span class="info-label">Mata Pelajaran (SMP)</span>
            <div class="info-value">
              <span class="icon">📚</span>
              <input type="text" :value="subjectSmpName || 'Tidak ada data'" readonly />
            </div>
          </div>

          <!-- Hanya Tampilkan Jika Guru SMA / SMP_SMA -->
          <div v-if="profile?.jenjang_guru === 'sma' || profile?.jenjang_guru === 'smp_sma'" class="info-group full-width">
            <span class="info-label">Mata Pelajaran (SMA)</span>
            <div class="info-value">
              <span class="icon">📖</span>
              <input type="text" :value="subjectSmaName || 'Tidak ada data'" readonly />
            </div>
          </div>
        </div>

        <div class="card-footer">
          <button type="button" class="btn primary-btn">Edit Informasi</button>
          <button type="button" class="btn outline-btn">Ganti Kata Sandi</button>
        </div>
      </section>
    </div>
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
  if (j === 'smp') return 'Guru Sekolah Menengah Pertama (SMP)';
  if (j === 'sma') return 'Guru Sekolah Menengah Atas (SMA)';
  if (j === 'smp_sma') return 'Guru SMP & SMA';
  return 'Guru SLAPUR';
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
.page-container {
  max-width: 900px;
  margin: 0 auto;
  animation: fadeIn 0.4s ease-out;
}

/* Banner Styles */
.profile-banner {
  position: relative;
  height: 200px;
  border-radius: 16px;
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  margin-bottom: 5rem; /* Space for the avatar overlapping */
  box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
}

.banner-content {
  position: absolute;
  bottom: -4rem;
  left: 2rem;
  display: flex;
  align-items: flex-end;
  gap: 1.5rem;
}

.avatar-wrapper {
  position: relative;
}

.avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #ffffff;
  color: #1e40af;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 800;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  border: 4px solid #f8fafc;
}

.edit-avatar-btn {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  border: 3px solid #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.edit-avatar-btn:hover {
  background: #2563eb;
  transform: scale(1.05);
}

.banner-text {
  padding-bottom: 0.5rem;
}

.profile-name {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem 0;
}

.profile-role {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

/* Card Styles */
.profile-content {
  padding: 0 1rem;
}

.info-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
  border: 1px solid #f1f5f9;
}

.card-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
}
.card-header h3 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 1.25rem;
}
.card-header p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
}

/* Grid & Form */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem 2rem;
}

.info-group.full-width {
  grid-column: 1 / -1;
}

.info-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}

.info-value {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.5rem 1rem;
  transition: border-color 0.2s;
}
.info-value:focus-within {
  border-color: #94a3b8;
}

.info-value .icon {
  margin-right: 0.75rem;
  font-size: 1.1rem;
  opacity: 0.7;
}

.info-value input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 0.95rem;
  color: #0f172a;
  font-weight: 500;
  outline: none;
  width: 100%;
}
.info-value input:read-only {
  color: #334155;
}

.status-badge {
  background: #dcfce7;
  color: #166534;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* Footer Actions */
.card-footer {
  margin-top: 2.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn {
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.primary-btn {
  background: #2563eb;
  color: white;
  border: none;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}
.primary-btn:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

.outline-btn {
  background: transparent;
  color: #475569;
  border: 1px solid #cbd5e1;
}
.outline-btn:hover {
  background: #f8fafc;
  color: #0f172a;
}

/* Loading state */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: #64748b;
  gap: 1rem;
}
.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .banner-content {
    flex-direction: column;
    align-items: center;
    left: 0;
    right: 0;
    bottom: -6rem;
    text-align: center;
  }
  .profile-banner {
    margin-bottom: 8rem;
  }
  .info-grid {
    grid-template-columns: 1fr;
  }
  .card-footer {
    flex-direction: column;
  }
  .btn {
    width: 100%;
  }
}
</style>
