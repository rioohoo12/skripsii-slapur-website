<template>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': isCollapsed }">
      <div class="sidebar-header">
        <div class="logo">
          <span class="logo-icon">🍽️</span>
          <span class="logo-text" v-if="!isCollapsed">Kafetaria</span>
        </div>
        <button class="toggle-btn" @click="isCollapsed = !isCollapsed">
          <span v-if="isCollapsed">❯</span>
          <span v-else>❮</span>
        </button>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/kafetaria/dashboard" class="nav-item">
          <span class="nav-icon">📊</span>
          <span class="nav-text" v-if="!isCollapsed">Dashboard</span>
        </router-link>
        
        <router-link to="/kafetaria/menus" class="nav-item">
          <span class="nav-icon">🍲</span>
          <span class="nav-text" v-if="!isCollapsed">Manajemen Menu</span>
        </router-link>
        
        <router-link to="/kafetaria/scanner" class="nav-item" active-class="active">
          <span class="nav-icon">📝</span>
          <span class="nav-text" v-if="!isCollapsed">Input Presensi</span>
        </router-link>

        <router-link to="/kafetaria/laporan" class="nav-item">
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

const userName = computed(() => authStore.user?.name || 'Staff');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

async function handleLogout() {
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
</style>
