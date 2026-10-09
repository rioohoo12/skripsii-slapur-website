<template>
  <div class="mahasiswa-layout">
    <!-- Siswa Sidebar Component with Mobile Drawer state -->
    <SiswaSidebar 
      v-model:mobileOpen="mobileSidebarOpen" 
    />

    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
          <!-- Hamburger Button on Mobile -->
          <button 
            class="hamburger-btn" 
            @click="mobileSidebarOpen = true"
            aria-label="Buka Menu Sidebar"
          >
            <span class="hamburger-icon">☰</span>
          </button>

          <!-- Search Input -->
          <div class="topbar-search">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Cari mata pelajaran, nilai, atau jadwal..." />
          </div>
        </div>

        <div class="topbar-actions">
          <div class="notification-wrap">
            <span class="bell-icon">🔔</span>
            <span class="badge">3</span>
          </div>

          <div class="profile-trigger" @click="profileOpen = !profileOpen">
            <div class="profile-info">
              <span class="profile-name">{{ namaSiswa }}</span>
              <span class="profile-role">{{ user.role || 'Siswa' }}</span>
            </div>
            <img 
              :src="user.avatar ? `http://localhost:8000/storage/${user.avatar}` : `https://i.pravatar.cc/150?u=${user.email || 'dea'}`" 
              alt="Avatar" 
              class="profile-avatar" 
            />
            <span class="profile-chevron" :class="{ open: profileOpen }">▼</span>
          </div>
          
          <Transition name="dropdown">
            <div v-if="profileOpen" class="profile-dropdown">
              <router-link :to="`/siswa/${jenisKelamin}/biodata`" class="dropdown-item">Profil / Biodata</router-link>
              <button type="button" class="dropdown-item logout" @click="handleLogout">
                Logout
              </button>
            </div>
          </Transition>
          <div v-if="profileOpen" class="backdrop" @click="profileOpen = false"></div>
        </div>
      </header>

      <!-- Main Router Content Area -->
      <div class="content-area">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import SiswaSidebar from '@/components/SiswaSidebar.vue';
import { useAuthStore } from '@/stores/auth';
import { usePendaftaranStore } from '@/stores/pendaftaran';

const props = defineProps({
  jenisKelamin: { type: String, default: 'laki-laki' }
});

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const pendaftaranStore = usePendaftaranStore();

const profileOpen = ref(false);
const mobileSidebarOpen = ref(false);

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const namaSiswa = computed(() => user.value.name || 'Siswa');

async function handleLogout() {
  profileOpen.value = false;
  await authStore.logout();
  router.push('/siswa');
}

function checkAccess(toPath) {
  if (pendaftaranStore.loading || pendaftaranStore.isComplete) return;
  
  const isAllowedRoute = toPath.includes('/pendaftaran') || toPath.includes('/biodata') || toPath.includes('/clearance') || toPath.includes('/administrasi') || toPath.includes('/tugas') || toPath.includes('/absensi');
  
  if (!isAllowedRoute) {
    alert('Anda harus menyelesaikan seluruh tahapan pendaftaran terlebih dahulu sebelum mengakses menu ini.');
    router.push(`/siswa/${props.jenisKelamin}/pendaftaran/form`);
  }
}

onMounted(async () => {
  await pendaftaranStore.fetchStatus();
  checkAccess(route.path);
});

watch(() => route.path, (newPath) => {
  checkAccess(newPath);
  mobileSidebarOpen.value = false; // close drawer on route change
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.mahasiswa-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
  background-color: #f5f7fb;
  color: #1e293b;
}

.main-content {
  flex: 1;
  min-width: 0;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  background: #ffffff;
  position: sticky;
  top: 0;
  z-index: 20;
  border-bottom: 1px solid #e2e8f0;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.hamburger-btn {
  display: none;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  font-size: 1.2rem;
  color: #334155;
  transition: background 0.2s;
}

.hamburger-btn:hover {
  background: #e2e8f0;
}

.topbar-search {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  padding: 0.55rem 1.1rem;
  max-width: 380px;
  width: 100%;
  gap: 0.75rem;
}

.topbar-search .search-icon {
  color: #94a3b8;
  font-size: 0.9rem;
}

.topbar-search input {
  border: none;
  background: transparent;
  width: 100%;
  outline: none;
  font-size: 0.85rem;
  color: #334155;
  font-family: inherit;
}

.topbar-search input::placeholder {
  color: #94a3b8;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  position: relative;
}

.notification-wrap {
  position: relative;
  cursor: pointer;
  padding: 0.5rem;
  color: #64748b;
  transition: color 0.2s;
}

.notification-wrap:hover {
  color: #0f1e3c;
}

.notification-wrap .bell-icon {
  font-size: 1.2rem;
}

.badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ef4444;
  color: white;
  font-size: 0.6rem;
  font-weight: bold;
  padding: 0.1rem 0.3rem;
  border-radius: 999px;
  border: 2px solid #ffffff;
}

.profile-trigger {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 999px;
  transition: background 0.2s;
}

.profile-trigger:hover {
  background: #f1f5f9;
}

.profile-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.profile-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
}

.profile-role {
  font-size: 0.7rem;
  color: #64748b;
}

.profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
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
  top: calc(100% + 0.5rem);
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  padding: 0.5rem;
  min-width: 170px;
  z-index: 30;
  border: 1px solid #e2e8f0;
}

.dropdown-item {
  display: block;
  width: 100%;
  padding: 0.55rem 0.9rem;
  text-align: left;
  background: none;
  border: none;
  border-radius: 8px;
  color: #334155;
  font-size: 0.85rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: #f1f5f9;
}

.dropdown-item.logout {
  color: #ef4444;
  margin-top: 0.25rem;
}

.dropdown-item.logout:hover {
  background: #fef2f2;
}

.backdrop {
  position: fixed;
  inset: 0;
  z-index: 25;
}

.content-area {
  flex: 1;
  padding: 1.75rem 2rem;
  overflow-y: auto;
}

/* Tablet & Mobile Responsiveness (< 1024px) */
@media (max-width: 1024px) {
  .main-content {
    margin-left: 0;
  }
  .hamburger-btn {
    display: block;
  }
  .topbar {
    padding: 0.85rem 1.25rem;
  }
  .topbar-search {
    max-width: 250px;
  }
  .content-area {
    padding: 1.25rem 1.25rem;
  }
}

/* Mobile Small (< 640px) */
@media (max-width: 640px) {
  .topbar-search {
    display: none; /* Hide search input on mobile screen to prevent clutter */
  }
  .profile-info {
    display: none; /* Show only avatar on small mobile screens */
  }
  .content-area {
    padding: 1rem 0.85rem;
  }
}
</style>
