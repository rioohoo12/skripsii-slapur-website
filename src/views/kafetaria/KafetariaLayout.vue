<template>
  <div class="layout">
    <!-- Mobile Backdrop -->
    <div 
      v-if="mobileSidebarOpen" 
      class="mobile-backdrop" 
      @click="mobileSidebarOpen = false"
      aria-hidden="true"
    ></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': isCollapsed, 'mobile-open': mobileSidebarOpen }">
      <div class="sidebar-header">
        <div class="logo">
          <span class="logo-icon">🍽️</span>
          <span class="logo-text" v-if="!isCollapsed">Kafetaria</span>
        </div>
        <button class="toggle-btn desktop-only" @click="isCollapsed = !isCollapsed">
          <span v-if="isCollapsed">❯</span>
          <span v-else>❮</span>
        </button>
        <button class="mobile-close-btn" @click="mobileSidebarOpen = false" aria-label="Tutup Menu">
          &times;
        </button>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/kafetaria/dashboard" class="nav-item" @click="mobileSidebarOpen = false">
          <span class="nav-icon">📊</span>
          <span class="nav-text" v-if="!isCollapsed">Dashboard</span>
        </router-link>
        
        <router-link to="/kafetaria/menus" class="nav-item" @click="mobileSidebarOpen = false">
          <span class="nav-icon">🍲</span>
          <span class="nav-text" v-if="!isCollapsed">Manajemen Menu</span>
        </router-link>
        
        <router-link to="/kafetaria/scanner" class="nav-item" active-class="active" @click="mobileSidebarOpen = false">
          <span class="nav-icon">📝</span>
          <span class="nav-text" v-if="!isCollapsed">Input Presensi</span>
        </router-link>

        <router-link to="/kafetaria/laporan" class="nav-item" @click="mobileSidebarOpen = false">
          <span class="nav-icon">📈</span>
          <span class="nav-text" v-if="!isCollapsed">Laporan Makan</span>
        </router-link>
      </nav>

      <div class="sidebar-footer" v-if="!isCollapsed">
        <div class="user-info">
          <div class="user-avatar">
            {{ userInitial }}
          </div>
          <div class="user-details">
            <span class="user-name">{{ userName }}</span>
            <span class="user-role">Staff Kafetaria</span>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Mobile Topbar Header -->
      <header class="kafetaria-mobile-topbar">
        <div class="mobile-topbar-left">
          <button class="hamburger-btn" @click="mobileSidebarOpen = true" aria-label="Buka Menu Kafetaria">
            <span class="hamburger-icon">☰</span>
          </button>
          <div class="mobile-topbar-brand">
            <span class="brand-icon">🍽️</span>
            <span class="brand-text">Kafetaria</span>
          </div>
        </div>
        <div class="mobile-user-badge">
          <div class="user-avatar-small">{{ userInitial }}</div>
        </div>
      </header>

      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const isCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const userName = computed(() => authStore.user?.name || 'Staff');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

async function handleLogout() {
  mobileSidebarOpen.value = false;
  await authStore.logout();
  router.push('/kafetaria');
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background-color: #f5f7fb;
  font-family: 'Inter', sans-serif;
}

.sidebar {
  width: 260px;
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: relative;
  z-index: 10;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e2e8f0;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  overflow: hidden;
  white-space: nowrap;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.toggle-btn {
  background: #f1f5f9;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.toggle-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.sidebar-nav {
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  overflow: hidden;
  white-space: nowrap;
}

.nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.nav-item.router-link-active {
  background: #fffbeb;
  color: #f59e0b;
}

.nav-icon {
  font-size: 1.25rem;
  min-width: 1.25rem;
  text-align: center;
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: #f59e0b;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.1rem;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.user-role {
  font-size: 0.75rem;
  color: #64748b;
}

.logout-btn {
  width: 100%;
  padding: 0.75rem;
  background: #fef2f2;
  color: #ef4444;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: #fee2e2;
}

.main-content {
  flex: 1;
  min-width: 0;
  height: 100vh;
  overflow-y: auto;
}

/* Mobile Drawer & Topbar Styles */
.mobile-close-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #64748b;
  cursor: pointer;
  line-height: 1;
  padding: 0.25rem 0.5rem;
}

.mobile-close-btn:hover {
  color: #ef4444;
}

.mobile-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(3px);
  z-index: 90;
}

.kafetaria-mobile-topbar {
  display: none;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 40;
}

.mobile-topbar-left {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.hamburger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  color: #1e293b;
  font-size: 1.2rem;
  transition: all 0.2s;
}

.hamburger-btn:hover {
  background: #f1f5f9;
}

.mobile-topbar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.brand-icon {
  font-size: 1.3rem;
}

.brand-text {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.user-avatar-small {
  width: 32px;
  height: 32px;
  background: #f59e0b;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.85rem;
}

@media (max-width: 1024px) {
  .desktop-only {
    display: none;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 275px;
    height: 100vh;
    z-index: 100;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .sidebar-header {
    justify-content: space-between;
  }

  .mobile-close-btn {
    display: block;
  }

  .kafetaria-mobile-topbar {
    display: flex;
  }
}
</style>
