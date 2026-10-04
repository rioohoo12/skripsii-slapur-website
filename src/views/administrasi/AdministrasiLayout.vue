<template>
  <div class="guru-layout">
    <aside class="guru-sidebar">
      <div class="sidebar-brand">
        <h2 class="brand-text">Sistem Staff</h2>
      </div>
      <div class="sidebar-user">
        <div class="sidebar-avatar">{{ inisialNama }}</div>
        <p class="sidebar-name">{{ user?.name || 'Staff Administrasi' }}</p>
        <p class="sidebar-role">{{ user?.email || 'staff@sekolah.com' }}</p>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/administrasi/dashboard" class="nav-item" active-class="active">
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Dashboard
        </router-link>
        <router-link to="/administrasi/pendaftaran" class="nav-item" active-class="active">
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          Pendaftaran
        </router-link>
        <router-link to="/administrasi/dokumen" class="nav-item" active-class="active">
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          Dokumen
        </router-link>
        <router-link to="/administrasi/pembayaran" class="nav-item" active-class="active">
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
          Pembayaran
        </router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="nav-item logout-btn">
          <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Logout
        </button>
      </div>
    </aside>
    
    <main class="guru-main">
      <header class="guru-topbar">
        <div class="topbar-left">
          <img src="/slapur-logo.png" alt="SLAPUR" class="topbar-logo" />
          <div class="topbar-titles">
            <h1 class="page-title">{{ $route.name?.replace('Administrasi', '') || 'Dashboard' }}</h1>
            <p class="page-desc">Staff Administrasi</p>
          </div>
        </div>
        <div class="topbar-right">
          <div
            class="profile-trigger"
            :aria-expanded="profileOpen"
            @click="profileOpen = !profileOpen"
          >
            <div class="profile-avatar">{{ inisialNama }}</div>
            <span class="profile-name">{{ user?.name || 'Staff' }}</span>
            <span class="profile-chevron" :class="{ open: profileOpen }">▼</span>
          </div>
          <Transition name="dropdown">
            <div v-if="profileOpen" class="profile-dropdown">
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
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const profileOpen = ref(false);

const user = computed(() => {
  try {
    return authStore.user || JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

const inisialNama = computed(() => {
  const n = (user.value?.name || 'Staff Administrasi').trim();
  const parts = n.split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return n.slice(0, 2).toUpperCase();
});

const handleLogout = () => {
  profileOpen.value = false;
  authStore.logout();
  router.push('/administrasi/login');
};
</script>

<style scoped>
.guru-layout {
  min-height: 100vh;
  display: flex;
  background: #f8fafc;
}

/* Sidebar Styles */
.guru-sidebar {
  width: 260px;
  background: #fff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  z-index: 50;
}
.sidebar-brand {
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid #e2e8f0;
}
.brand-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.sidebar-user {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}
.sidebar-avatar {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}
.sidebar-name {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem;
}
.sidebar-role {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
}
.sidebar-nav {
  flex: 1;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  overflow-y: auto;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #475569;
  text-decoration: none;
  font-weight: 500;
  border-radius: 10px;
  transition: all 0.2s;
  font-size: 0.9rem;
}
.nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}
.nav-item.active {
  background: #eff6ff;
  color: #2563eb;
  font-weight: 600;
}
.nav-icon {
  width: 20px;
  height: 20px;
}
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid #e2e8f0;
}
.logout-btn {
  width: 100%;
  border: none;
  background: transparent;
  color: #ef4444;
  cursor: pointer;
  font-family: inherit;
}
.logout-btn:hover {
  background: #fef2f2;
}

/* Main Content Styles */
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
  background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
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
  .guru-sidebar {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
  }
}
</style>
