<template>
  <div class="guru-layout">
    <GuruSidebar />
    <main class="guru-main">
      <header class="guru-topbar">
        <div class="topbar-left">
          <img src="/slapur-logo.png" alt="SLAPUR" class="topbar-logo" />
          <div class="topbar-titles">
            <h1 class="page-title">{{ pageTitle }}</h1>
            <p class="page-desc">Dashboard Guru</p>
          </div>
        </div>
        <div class="topbar-right">
          <div
            class="profile-trigger"
            :aria-expanded="profileOpen"
            @click="profileOpen = !profileOpen"
          >
            <div class="profile-avatar">{{ inisialNama }}</div>
            <span class="profile-name">{{ user?.name || 'Guru' }}</span>
            <span class="profile-chevron" :class="{ open: profileOpen }">▼</span>
          </div>
          <Transition name="dropdown">
            <div v-if="profileOpen" class="profile-dropdown">
              <router-link to="/guru/profile" class="profile-dropdown-item" @click="profileOpen = false">
                Profil Guru
              </router-link>
              <button type="button" class="profile-dropdown-item logout" @click="handleLogout">
                Logout
              </button>
            </div>
          </Transition>
          <div v-if="profileOpen" class="profile-backdrop" @click="profileOpen = false" aria-hidden="true" />
        </div>
      </header>
      <div class="guru-content">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import GuruSidebar from '@/components/GuruSidebar.vue';
import { authApi } from '@/api/auth';

const route = useRoute();
const router = useRouter();
const profileOpen = ref(false);

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const inisialNama = computed(() => {
  const n = (user.value?.name || '').trim();
  if (!n) return 'G';
  const parts = n.split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return n.slice(0, 2).toUpperCase();
});

const pageTitle = computed(() => {
  const name = route.name || '';
  const map = {
    GuruDashboard: 'Dashboard',
    GuruKelas: 'Data Kelas',
    GuruJadwal: 'Jadwal Mengajar',
    GuruAbsensi: 'Absensi Siswa',
    GuruNilai: 'Nilai Siswa',
    GuruTugas: 'Tugas',
    GuruMateri: 'Materi',
    GuruPengumuman: 'Pengumuman',
    GuruLaporan: 'Laporan',
    GuruProfile: 'Profil Guru',
  };
  return map[name] || 'Dashboard';
});

function handleLogout() {
  profileOpen.value = false;
  authApi.setToken(null);
  localStorage.removeItem('user');
  router.push('/guru');
}
</script>

<style scoped>
.guru-layout {
  min-height: 100vh;
  display: flex;
  background: #f8fafc;
}
.guru-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.guru-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1.5rem;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  position: sticky;
  top: 0;
  z-index: 40;
}
.topbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.topbar-logo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: cover;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.topbar-titles {
  margin: 0;
}
.page-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.page-desc {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0.25rem 0 0 0;
}
.topbar-right {
  position: relative;
}
.profile-trigger {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.4rem 0.75rem;
  border-radius: 10px;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  background: #fff;
  transition: background 0.2s, border-color 0.2s;
}
.profile-trigger:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}
.profile-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
}
.profile-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #334155;
}
.profile-chevron {
  font-size: 0.65rem;
  color: #64748b;
  transition: transform 0.2s;
}
.profile-chevron.open {
  transform: rotate(180deg);
}
.profile-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  min-width: 200px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  border: 1px solid #e2e8f0;
  padding: 0.5rem;
  z-index: 50;
}
.profile-dropdown-item {
  display: block;
  width: 100%;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 8px;
  background: none;
  color: #334155;
  font-size: 0.9rem;
  text-align: left;
  cursor: pointer;
  text-decoration: none;
  font-family: inherit;
  transition: background 0.2s;
}
.profile-dropdown-item:hover {
  background: #f1f5f9;
}
.profile-dropdown-item.logout {
  color: #dc2626;
}
.profile-dropdown-item.logout:hover {
  background: #fef2f2;
}
.profile-backdrop {
  position: fixed;
  inset: 0;
  z-index: 45;
}
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
.guru-content {
  flex: 1;
  padding: 1.5rem;
  overflow: auto;
}
@media (max-width: 900px) {
  .guru-layout {
    flex-direction: column;
  }
}
</style>
