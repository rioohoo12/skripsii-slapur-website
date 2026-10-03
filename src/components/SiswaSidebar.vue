<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-wrap">
        <img src="/slapur-logo.png" alt="Slapur Academic" class="brand-logo" />
        <div class="brand-text">
          <span class="brand-name">Slapur</span>
          <span class="brand-sub">Academic</span>
        </div>
      </div>
    </div>
    
    <nav class="sidebar-nav">
      <template v-for="item in menuItems" :key="item.path">
        <router-link
          v-if="!item.locked"
          :to="item.path"
          class="nav-item"
          active-class="active"
          :exact-active-class="item.exact ? 'active' : ''"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
        
        <div v-else class="nav-item locked" @click="showLockedAlert">
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label">{{ item.label }} <span class="lock-icon">🔒</span></span>
        </div>
      </template>
    </nav>
    
    <div class="sidebar-footer">
      <button class="logout-btn" @click="handleLogout">
        <span class="nav-icon">🚪</span>
        <span class="nav-label">Keluar</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { usePendaftaranStore } from '@/stores/pendaftaran';

const router = useRouter();
const authStore = useAuthStore();
const pendaftaranStore = usePendaftaranStore();

const jenisKelamin = computed(() => {
  return authStore.user?.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
});

const menuItems = computed(() => {
  const isComplete = pendaftaranStore.isComplete;
  return [
    { path: `/siswa/${jenisKelamin.value}/dashboard`, label: 'Beranda', icon: '🏠', exact: true, locked: false },
    { path: `/siswa/${jenisKelamin.value}/jadwal`, label: 'Jadwal Pelajaran', icon: '📅', locked: !isComplete },
    { path: `/siswa/${jenisKelamin.value}/dining`, label: 'Dining', icon: '🍽️', locked: !isComplete },
    { path: `/siswa/${jenisKelamin.value}/asrama`, label: 'Asrama', icon: '🏰', locked: !isComplete },
    { path: `/siswa/${jenisKelamin.value}/pendaftaran/status`, label: 'Pendaftaran', icon: '📋', locked: false },
    { path: `/siswa/${jenisKelamin.value}/grade`, label: 'Nilai', icon: '📊', locked: !isComplete },
    { path: `/siswa/${jenisKelamin.value}/absensi`, label: 'Presensi', icon: '✅', locked: false },
    { path: `/siswa/${jenisKelamin.value}/tugas`, label: 'Tugas', icon: '📝', locked: false },
    { path: `/siswa/${jenisKelamin.value}/keuangan`, label: 'Tagihan', icon: '💰', locked: !isComplete },
    { path: `/siswa/${jenisKelamin.value}/biodata`, label: 'Profil', icon: '👤', locked: false },
  ];
});

function showLockedAlert() {
  alert('Silakan selesaikan proses pendaftaran Anda terlebih dahulu untuk membuka menu ini.');
}

async function handleLogout() {
  await authStore.logout();
  router.push('/siswa');
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  background-color: #0f1e3c;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 20;
}

.sidebar-header {
  padding: 2rem 1.5rem;
}

.logo-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.brand-logo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.brand-sub {
  font-size: 0.95rem;
  font-weight: 400;
  color: #94a3b8;
}

.sidebar-nav {
  flex: 1;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  color: #cbd5e1;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
}

.nav-item.locked {
  opacity: 0.5;
  cursor: not-allowed;
}

.lock-icon {
  font-size: 0.8rem;
  margin-left: 0.5rem;
  opacity: 0.8;
}

.nav-item:hover:not(.locked) {
  background-color: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.nav-item.active {
  background-color: #3b82f6;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.nav-icon {
  font-size: 1.25rem;
  width: 1.5rem;
  text-align: center;
}

.sidebar-footer {
  padding: 1.5rem;
  margin-top: auto;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  padding: 0.85rem 1rem;
  background: transparent;
  border: none;
  border-radius: 12px;
  color: #94a3b8;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  font-family: inherit;
}

.logout-btn:hover {
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

@media (max-width: 1024px) {
  .sidebar {
    width: 100%;
    min-height: auto;
    position: static;
  }
}
</style>
