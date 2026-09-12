<template>
  <nav class="siswa-menu">
    <ul>
      <li>
        <a
          :class="{ active: activeMenu === 'dashboard' }"
          @click="setMenu('dashboard')"
          >Dashboard</a
        >
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'biodata' }"
          @click="setMenu('biodata')"
          >Biodata</a
        >
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'keuangan' }"
          @click="setMenu('keuangan')"
          >Keuangan</a
        >
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'kafetaria' }"
          @click="setMenu('kafetaria')"
          >Kafetaria</a
        >
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'absensi' }"
          @click="setMenu('absensi')"
          >Absensi</a
        >
      </li>
      <li>
        <a :class="{ active: activeMenu === 'kelas' }" @click="setMenu('kelas')"
          >Data Kelas</a
        >
        <ul v-if="activeMenu === 'kelas'" class="submenu">
          <li><a @click="setSubmenu('review')">Review Kelas</a></li>
        </ul>
      </li>
      <li>
        <a :class="{ active: activeMenu === 'grade' }" @click="setMenu('grade')"
          >Grade/Nilai</a
        >
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'pendaftaran' }"
          @click="setMenu('pendaftaran')"
          >Pendaftaran</a
        >
        <ul v-if="activeMenu === 'pendaftaran'" class="submenu">
          <li><a @click="setSubmenu('status')">Status Pendaftaran</a></li>
          <li>
            <a @click="setSubmenu('permohonan')">Permohonan Pendaftaran</a>
          </li>
          <li><a @click="setSubmenu('kamar')">Pilih Kamar</a></li>
          <li><a @click="setSubmenu('kurikulum')">Kurikulum</a></li>
        </ul>
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'clearance' }"
          @click="setMenu('clearance')"
          >Clearance Slip</a
        >
        <ul v-if="activeMenu === 'clearance'" class="submenu">
          <li><a @click="setSubmenu('pembayaran-pendaftaran')">Pembayaran Pendaftaran</a></li>
          <li><a @click="setSubmenu('mid')">MID</a></li>
          <li><a @click="setSubmenu('final')">Final Test</a></li>
        </ul>
      </li>
      <li>
        <a
          :class="{ active: activeMenu === 'administrasi' }"
          @click="setMenu('administrasi')"
          >Administrasi</a
        >
        <ul v-if="activeMenu === 'administrasi'" class="submenu">
          <li><a @click="setSubmenu('surat')">Permohonan Surat</a></li>
          <li><a @click="setSubmenu('dokumen')">Upload Dokumen</a></li>
          <li><a @click="setSubmenu('ekstrakurikuler')">Ekstrakurikuler</a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <aside class="siswa-sidebar">
    <div class="sidebar-header">
      <img src="/slapur-logo.png" alt="Logo SLAPUR" class="sidebar-logo" />
      <span class="sidebar-title">SLAPUR System</span>
    </div>
    <ul class="sidebar-list">
      <li :class="{ active: activeMenu === 'dashboard' }">
        <button @click="setMenu('dashboard')">
          <span class="icon">🏠</span> Dashboard
        </button>
      </li>
      <li :class="{ active: activeMenu === 'biodata' }">
        <button @click="setMenu('biodata')">
          <span class="icon">👤</span> Biodata
        </button>
      </li>
      <li :class="{ active: activeMenu === 'keuangan' }">
        <button @click="setMenu('keuangan')">
          <span class="icon">💰</span> Keuangan
        </button>
      </li>
      <li :class="{ active: activeMenu === 'kafetaria' }">
        <button @click="setMenu('kafetaria')">
          <span class="icon">🍽️</span> Kafetaria
        </button>
      </li>
      <li :class="{ active: activeMenu === 'absensi' }">
        <button @click="setMenu('absensi')">
          <span class="icon">📝</span> Absensi
        </button>
      </li>
      <li :class="{ active: activeMenu === 'kelas' }">
        <button @click="setMenu('kelas')">
          <span class="icon">🏫</span> Data Kelas
        </button>
        <ul v-if="activeMenu === 'kelas'" class="sidebar-submenu">
          <li><button @click="setSubmenu('review')">Review Kelas</button></li>
        </ul>
      </li>
      <li :class="{ active: activeMenu === 'grade' }">
        <button @click="setMenu('grade')">
          <span class="icon">📊</span> Grade/Nilai
        </button>
      </li>
      <li :class="{ active: activeMenu === 'pendaftaran' }">
        <button @click="setMenu('pendaftaran')">
          <span class="icon">📝</span> Pendaftaran
        </button>
        <ul v-if="activeMenu === 'pendaftaran'" class="sidebar-submenu">
          <li>
            <button @click="setSubmenu('status')">Status Pendaftaran</button>
          </li>
          <li>
            <button @click="setSubmenu('permohonan')">
              Permohonan Pendaftaran
            </button>
          </li>
          <li><button @click="setSubmenu('kamar')">Pilih Kamar</button></li>
          <li><button @click="setSubmenu('kurikulum')">Kurikulum</button></li>
        </ul>
      </li>
      <li :class="{ active: activeMenu === 'clearance' }">
        <button @click="setMenu('clearance')">
          <span class="icon">✅</span> Clearance Slip
        </button>
        <ul v-if="activeMenu === 'clearance'" class="sidebar-submenu">
          <li><button @click="setSubmenu('mid')">MID</button></li>
          <li><button @click="setSubmenu('final')">Final Test</button></li>
        </ul>
      </li>
      <li :class="{ active: activeMenu === 'administrasi' }">
        <button @click="setMenu('administrasi')">
          <span class="icon">📁</span> Administrasi
        </button>
        <ul v-if="activeMenu === 'administrasi'" class="sidebar-submenu">
          <li>
            <button @click="setSubmenu('surat')">Permohonan Surat</button>
          </li>
          <li>
            <button @click="setSubmenu('dokumen')">Upload Dokumen</button>
          </li>
          <li>
            <button @click="setSubmenu('ekstrakurikuler')">
              Ekstrakurikuler
            </button>
          </li>
        </ul>
      </li>
    </ul>
  </aside>
</template>

<script setup>
import { ref } from "vue";
const activeMenu = ref("dashboard");
const activeSubmenu = ref("");
function setMenu(menu) {
  activeMenu.value = menu;
  activeSubmenu.value = "";
}
function setSubmenu(sub) {
  activeSubmenu.value = sub;
}
</script>

<style scoped>
.siswa-menu {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
  padding: 1.5rem;
  margin-bottom: 2rem;
}
.siswa-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.siswa-menu > ul > li {
  margin-bottom: 0.5rem;
}
.siswa-menu a {
  display: block;
  padding: 0.7rem 1rem;
  border-radius: 8px;
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}
.siswa-menu a.active,
.siswa-menu a:hover {
  background: #e6f0ff;
  color: #1976d2;
}
.submenu {
  margin-left: 1.5rem;
  margin-top: 0.3rem;
}
.submenu li {
  margin-bottom: 0.3rem;
}
.submenu a {
  font-size: 0.95em;
  color: #555;
}
.submenu a:hover {
  color: #1976d2;
  background: #f0f7ff;
}
.siswa-sidebar {
  width: 240px;
  min-height: 100vh;
  background: linear-gradient(180deg, #1976d2 0%, #fff 100%);
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(25, 118, 210, 0.08);
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}
.sidebar-header {
  display: flex;
  align-items: center;
  margin-bottom: 2rem;
}
.sidebar-logo {
  width: 40px;
  height: 40px;
  margin-right: 1rem;
}
.sidebar-title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #1976d2;
}
.sidebar-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar-list > li {
  margin-bottom: 0.5rem;
}
.sidebar-list button {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.7rem 1rem;
  border: none;
  border-radius: 8px;
  background: none;
  color: #333;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition:
    background 0.2s,
    color 0.2s;
}
.sidebar-list li.active > button,
.sidebar-list button:hover {
  background: #e6f0ff;
  color: #1976d2;
}
.icon {
  font-size: 1.2em;
}
.sidebar-submenu {
  margin-left: 2rem;
  margin-top: 0.3rem;
}
.sidebar-submenu li {
  margin-bottom: 0.3rem;
}
.sidebar-submenu button {
  font-size: 0.95em;
  color: #555;
  background: none;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  transition:
    color 0.2s,
    background 0.2s;
}
.sidebar-submenu button:hover {
  color: #1976d2;
  background: #f0f7ff;
}
@media (max-width: 900px) {
  .siswa-sidebar {
    width: 100%;
    min-height: auto;
    border-radius: 0;
    padding: 1rem 0.5rem;
  }
}
</style>
