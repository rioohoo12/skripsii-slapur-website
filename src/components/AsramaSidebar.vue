<template>
  <div>
    <!-- Mobile Backdrop -->
    <div 
      v-if="mobileOpen" 
      class="mobile-backdrop"
      @click="closeMobile"
    ></div>

    <aside 
      class="sidebar" 
      :class="[isPutri ? 'sidebar-putri' : 'sidebar-putra', { 'mobile-open': mobileOpen }]"
    >
      <div class="sidebar-header">
        <div class="logo-wrap">
          <img src="/slapur-logo.png" :alt="label" class="brand-logo" />
          <div class="brand-text">
            <span class="brand-name">Slapur</span>
            <span class="brand-sub">{{ label }}</span>
          </div>
        </div>
        <button class="mobile-close-btn" @click="closeMobile" aria-label="Tutup Menu">
          &times;
        </button>
      </div>
      
      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          active-class="active"
          :exact-active-class="item.exact ? 'active' : ''"
          @click="closeMobile"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
      </nav>
      
      <div class="sidebar-footer">
        <button class="logout-btn" @click="handleLogout">
          <span class="nav-icon">🚪</span>
          <span class="nav-label">Keluar</span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useAsramaType } from '@/composables/useAsramaType';

const props = defineProps({
  mobileOpen: { type: Boolean, default: false }
});

const emit = defineEmits(['update:mobileOpen', 'close']);

const router = useRouter();
const authStore = useAuthStore();
const { basePath, label, isPutri } = useAsramaType();

const menuItems = computed(() => [
  { path: `${basePath.value}/dashboard`, label: 'Dashboard', icon: '📊', exact: true },
  { path: `${basePath.value}/kamar`, label: 'Manajemen Kamar', icon: '🛏️', exact: true },
  { path: `${basePath.value}/persetujuan`, label: 'Persetujuan Kamar', icon: '✅', exact: true },
  { path: `${basePath.value}/penghuni`, label: 'Penghuni & Mutasi', icon: '👥', exact: true },
]);

function closeMobile() {
  emit('update:mobileOpen', false);
  emit('close');
}

async function handleLogout() {
  closeMobile();
  await authStore.logout();
  router.push(basePath.value);
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 20;
  transition: background-color 0.25s ease;
}

.sidebar-putra {
  background-color: #0f1e3c;
}

.sidebar-putri {
  background-color: #2e1065;
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
  background: white;
  padding: 2px;
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
  color: #c4b5fd;
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
  color: #ddd6fe;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.nav-item.active {
  background-color: #8b5cf6;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
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
  color: #c4b5fd;
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

.mobile-close-btn {
  display: none;
  background: none;
  border: none;
  font-size: 1.75rem;
  color: #c4b5fd;
  cursor: pointer;
  line-height: 1;
  padding: 0.25rem 0.5rem;
}

.mobile-close-btn:hover {
  color: #ffffff;
}

.mobile-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(3px);
  z-index: 90;
}

@media (max-width: 1024px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 275px;
    height: 100vh;
    z-index: 100;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .sidebar.mobile-open {
    transform: translateX(0);
  }

  .sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 1.25rem;
  }

  .mobile-close-btn {
    display: block;
  }
}
</style>
