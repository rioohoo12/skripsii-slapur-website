<template>
  <aside class="guru-sidebar">
    <div class="sidebar-header">
      <div class="logo-wrap">
        <img src="/slapur-logo.png" alt="Logo SLAPUR" class="sidebar-logo" />
        <span class="sidebar-title">SLAPUR</span>
      </div>
      <p class="sidebar-subtitle">Dashboard Guru</p>
    </div>
    <nav class="sidebar-nav">
      <router-link
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        class="nav-item"
        :class="{ active: isActive(item) }"
      >
        <span class="nav-icon" v-html="item.icon"></span>
        <span class="nav-label">{{ item.label }}</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const iconGauge = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
const iconClass = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18"/><path d="M3 12h18"/><path d="M3 17h18"/></svg>';
const iconCalendar = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>';
const iconCheck = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>';
const iconScore = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-6"/></svg>';
const iconTask = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>';
const iconBook = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2Z"/></svg>';
const iconMegaphone = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11 13v6"/><path d="M7 12v8"/></svg>';
const iconReport = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>';
const iconUser = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';

const menuItems = [
  { label: 'Dashboard', icon: iconGauge, to: '/guru/dashboard' },
  { label: 'Data Kelas', icon: iconClass, to: '/guru/kelas' },
  { label: 'Jadwal Mengajar', icon: iconCalendar, to: '/guru/jadwal' },
  { label: 'Absensi Siswa', icon: iconCheck, to: '/guru/absensi' },
  { label: 'Nilai Siswa', icon: iconScore, to: '/guru/nilai' },
  { label: 'Tugas', icon: iconTask, to: '/guru/tugas' },
  { label: 'Materi', icon: iconBook, to: '/guru/materi' },
  { label: 'Profil Guru', icon: iconUser, to: '/guru/profile' },
];

function isActive(item) {
  const path = route.path.replace(/\/$/, '') || '';
  if (path === item.to) return true;
  if (path.startsWith(item.to + '/')) return true;
  return false;
}
</script>

<style scoped>
.guru-sidebar {
  width: 260px;
  min-height: 100vh;
  background: #1e293b;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}
.sidebar-header {
  padding: 1.25rem 1rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.logo-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.sidebar-logo {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  object-fit: cover;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
.sidebar-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
}
.sidebar-subtitle {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0.5rem 0 0 0;
}
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 0.5rem;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.7rem 1rem;
  border: none;
  border-radius: 10px;
  background: none;
  color: #94a3b8;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s ease;
  text-align: left;
  font-family: inherit;
  margin-bottom: 2px;
}
.nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #e2e8f0;
}
.nav-item.active {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}
.nav-item.active .nav-icon :deep(svg) {
  stroke: #3b82f6;
}
.nav-item:hover .nav-icon :deep(svg) {
  stroke: #e2e8f0;
}
.nav-item.active .nav-icon :deep(svg) {
  stroke: #3b82f6;
}
.nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.nav-icon :deep(svg) {
  stroke: #94a3b8;
  transition: stroke 0.2s;
}
.nav-label {
  flex: 1;
}
@media (max-width: 900px) {
  .guru-sidebar {
    width: 100%;
    min-height: auto;
  }
}
</style>
