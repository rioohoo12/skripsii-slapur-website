<template>
  <div class="staff-dashboard">
    <header class="staff-topbar">
      <h1 class="staff-title">Dashboard Staff</h1>
      <div class="staff-user">
        <span class="staff-name">{{ user?.name || 'Staff' }}</span>
        <button type="button" class="btn-logout" @click="handleLogout">Logout</button>
      </div>
    </header>
    <main class="staff-content">
      <div class="welcome-card">
        <h2>Selamat datang, {{ user?.name || 'Staff' }}</h2>
        <p>Ini adalah halaman dashboard untuk staff. Fitur lengkap dapat dikembangkan lebih lanjut.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { authApi } from '@/api/auth';

const router = useRouter();
const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || '{}');
  } catch {
    return {};
  }
});

function handleLogout() {
  authApi.setToken(null);
  localStorage.removeItem('user');
  router.push('/staff');
}
</script>

<style scoped>
.staff-dashboard {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #f0fdfa 0%, #ccfbf1 40%, #f8fafc 100%);
}
.staff-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 2rem;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  border-left: 4px solid #14b8a6;
}
.staff-title { font-size: 1.35rem; font-weight: 700; color: #1e293b; margin: 0; }
.staff-user { display: flex; align-items: center; gap: 1rem; }
.staff-name { font-weight: 600; color: #0d9488; }
.btn-logout {
  padding: 0.5rem 1rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #fff;
  color: #64748b;
  font-size: 0.9rem;
  cursor: pointer;
}
.btn-logout:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.staff-content { flex: 1; padding: 2rem; }
.welcome-card {
  max-width: 600px;
  padding: 2rem;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  border-top: 4px solid #14b8a6;
}
.welcome-card h2 { font-size: 1.25rem; color: #1e293b; margin: 0 0 0.75rem 0; }
.welcome-card p { color: #64748b; margin: 0; line-height: 1.6; }
</style>
