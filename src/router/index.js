import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'Home',
    redirect: () => ({ path: '/siswa' }),
  },
  {
    path: '/siswa',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/Register.vue'),
  },
  {
    path: '/guru',
    name: 'LoginGuru',
    component: () => import('@/views/LoginGuru.vue'),
  },
  {
    path: '/guru/buat-akun',
    name: 'BuatAkunGuru',
    component: () => import('@/views/guru/BuatAkunGuru.vue'),
  },
  {
    path: '/guru/lupa-password',
    name: 'LupaPasswordGuru',
    component: () => import('@/views/guru/LupaPasswordGuru.vue'),
  },
  {
    path: '/guru/reset-password',
    name: 'ResetPasswordGuru',
    component: () => import('@/views/guru/ResetPasswordGuru.vue'),
  },
  {
    path: '/guru',
    component: () => import('@/views/guru/GuruLayout.vue'),
    children: [
      { path: '', redirect: '/guru/dashboard' },
      { path: 'dashboard', name: 'GuruDashboard', component: () => import('@/views/guru/DashboardGuruHome.vue') },
      { path: 'kelas', name: 'GuruKelas', component: () => import('@/views/guru/GuruKelas.vue') },
      { path: 'jadwal', name: 'GuruJadwal', component: () => import('@/views/guru/GuruJadwal.vue') },
      { path: 'absensi', name: 'GuruAbsensi', component: () => import('@/views/guru/GuruAbsensi.vue') },
      { path: 'nilai', name: 'GuruNilai', component: () => import('@/views/guru/GuruNilai.vue') },
      { path: 'tugas', name: 'GuruTugas', component: () => import('@/views/guru/GuruTugas.vue') },
      { path: 'materi', name: 'GuruMateri', component: () => import('@/views/guru/GuruMateri.vue') },
      { path: 'pengumuman', name: 'GuruPengumuman', component: () => import('@/views/guru/GuruPengumuman.vue') },
      { path: 'laporan', name: 'GuruLaporan', component: () => import('@/views/guru/GuruLaporan.vue') },
      { path: 'profile', name: 'GuruProfile', component: () => import('@/views/guru/GuruAkun.vue') },
    ],
  },
  {
    path: '/administrasi',
    redirect: '/administrasi/login'
  },
  {
    path: '/administrasi/login',
    name: 'LoginAdministrasi',
    component: () => import('@/views/administrasi/LoginAdministrasi.vue'),
  },
  {
    path: '/administrasi',
    component: () => import('@/views/administrasi/AdministrasiLayout.vue'),
    children: [
      { path: 'dashboard', name: 'DashboardAdministrasi', component: () => import('@/views/administrasi/DashboardAdministrasi.vue') },
      { path: 'siswa', name: 'AdministrasiSiswa', component: () => import('@/views/administrasi/SiswaList.vue') },
      { path: 'siswa/:id', name: 'AdministrasiSiswaDetail', component: () => import('@/views/administrasi/SiswaDetail.vue') },
      { path: 'dokumen', name: 'AdministrasiDokumen', component: () => import('@/views/administrasi/DokumenList.vue') },
      { path: 'dokumen/:id', name: 'AdministrasiDokumenDetail', component: () => import('@/views/administrasi/DokumenDetail.vue') },
      { path: 'pembayaran', name: 'AdministrasiPembayaran', component: () => import('@/views/administrasi/PembayaranList.vue') },
      { path: 'pembayaran/:id', name: 'AdministrasiPembayaranDetail', component: () => import('@/views/administrasi/PembayaranDetail.vue') },
      { path: 'tagihan', name: 'AdministrasiTagihan', component: () => import('@/views/administrasi/Tagihan.vue') },
      { path: 'laporan', name: 'AdministrasiLaporan', component: () => import('@/views/administrasi/Laporan.vue') },
    ],
  },
  {
    path: '/asrama',
    name: 'LoginAsrama',
    component: () => import('@/views/asrama/LoginAsrama.vue'),
  },
  {
    path: '/asrama',
    component: () => import('@/views/asrama/AsramaLayout.vue'),
    children: [
      { path: '', redirect: '/asrama/dashboard' },
      { path: 'dashboard', name: 'DashboardAsrama', component: () => import('@/views/asrama/DashboardAsrama.vue') },
      { path: 'kamar', name: 'ManajemenKamar', component: () => import('@/views/asrama/ManajemenKamar.vue') },
      { path: 'persetujuan', name: 'PersetujuanKamar', component: () => import('@/views/asrama/PersetujuanKamar.vue') },
      { path: 'penghuni', name: 'PenghuniKamar', component: () => import('@/views/asrama/PenghuniKamar.vue') },
    ],
  },
  {
    path: '/kafetaria',
    name: 'LoginKafetaria',
    component: () => import('@/views/kafetaria/LoginKafetaria.vue'),
  },
  {
    path: '/kafetaria',
    component: () => import('@/views/kafetaria/KafetariaLayout.vue'),
    children: [
      { path: '', redirect: '/kafetaria/dashboard' },
      { path: 'dashboard', name: 'DashboardKafetaria', component: () => import('@/views/kafetaria/DashboardKafetaria.vue') },
      { path: 'menus', name: 'ManajemenMenu', component: () => import('@/views/kafetaria/ManajemenMenu.vue') },
      { path: 'scanner', name: 'KafetariaScanner', component: () => import('@/views/kafetaria/KafetariaScanner.vue') },
      { path: 'laporan', name: 'KafetariaLaporan', component: () => import('@/views/kafetaria/KafetariaLaporan.vue') },
    ],
  },
  {
    path: '/siswa/:jenisKelamin',
    component: () => import('@/views/DashboardSiswa.vue'),
    props: true,
    children: [
      { path: '', redirect: (r) => ({ path: `/siswa/${r.params.jenisKelamin}/dashboard` }) },
      { path: 'dashboard', name: 'Dashboard', component: () => import('@/views/siswa/DashboardHome.vue') },
      { path: 'jadwal', name: 'JadwalPelajaran', component: () => import('@/views/siswa/JadwalPelajaran.vue') },
      { path: 'dining', name: 'Dining', component: () => import('@/views/siswa/Dining.vue') },
      { path: 'asrama', name: 'Asrama', component: () => import('@/views/siswa/Asrama.vue') },
      { path: 'biodata', name: 'Biodata', component: () => import('@/views/siswa/Biodata.vue') },
      { path: 'keuangan', name: 'Keuangan', component: () => import('@/views/siswa/Keuangan.vue') },
      { path: 'kafetaria', name: 'Kafetaria', component: () => import('@/views/siswa/Kafetaria.vue') },
      { path: 'absensi', name: 'Absensi', component: () => import('@/views/siswa/Absensi.vue') },
      { path: 'data-kelas', name: 'DataKelas', component: () => import('@/views/siswa/DataKelas.vue') },
      { path: 'data-kelas/review', name: 'ReviewKelas', component: () => import('@/views/siswa/ReviewKelas.vue') },
      { path: 'grade', name: 'GradeNilai', component: () => import('@/views/siswa/GradeNilai.vue') },
      { path: 'pendaftaran', name: 'Pendaftaran', component: () => import('@/views/siswa/PendaftaranStatus.vue') },
      { path: 'pendaftaran/form', name: 'FormPendaftaran', component: () => import('@/views/siswa/PendaftaranForm.vue') },
      { path: 'pendaftaran/status', name: 'StatusPendaftaran', component: () => import('@/views/siswa/PendaftaranStatus.vue') },
      { path: 'pendaftaran/permohonan', name: 'PermohonanPendaftaran', component: () => import('@/views/siswa/PendaftaranPermohonan.vue') },
      { path: 'pendaftaran/kamar', name: 'PilihKamar', component: () => import('@/views/siswa/PilihKamar.vue') },
      { path: 'pendaftaran/kurikulum', name: 'Kurikulum', component: () => import('@/views/siswa/Kurikulum.vue') },
      { path: 'pendaftaran/dokumen', name: 'PendaftaranDokumen', component: () => import('@/views/siswa/AdministrasiDokumen.vue') },
      { path: 'clearance', name: 'ClearanceSlip', component: () => import('@/views/siswa/ClearanceMid.vue') },
      { path: 'clearance/pembayaran-pendaftaran', name: 'ClearancePembayaran', component: () => import('@/views/siswa/ClearancePembayaran.vue') },
      { path: 'clearance/mid', name: 'ClearanceMid', component: () => import('@/views/siswa/ClearanceMid.vue') },
      { path: 'clearance/final', name: 'ClearanceFinal', component: () => import('@/views/siswa/ClearanceFinal.vue') },
      { path: 'administrasi', name: 'Administrasi', component: () => import('@/views/siswa/AdministrasiSurat.vue') },
      { path: 'administrasi/surat', name: 'PermohonanSurat', component: () => import('@/views/siswa/AdministrasiSurat.vue') },
      { path: 'administrasi/dokumen', name: 'UploadDokumen', component: () => import('@/views/siswa/AdministrasiDokumen.vue') },
      { path: 'administrasi/ekstrakurikuler', name: 'Ekstrakurikuler', component: () => import('@/views/siswa/AdministrasiEkstrakurikuler.vue') },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

import { useAuthStore } from '@/stores/auth';

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  // Ensure store is initialized from localStorage on first load
  if (!authStore.token && localStorage.getItem('auth_token')) {
    authStore.init();
  }

  const toSiswa = to.path.startsWith('/siswa/');
  const toGuru = to.path.startsWith('/guru');
  const toAdministrasi = to.path.startsWith('/administrasi');

  const isLoggedIn = authStore.isLoggedIn;
  const user = authStore.user;
  const isGuruRole = authStore.isGuru;
  const isAdministrasiRole = authStore.isStaff;

  if (toGuru) {
    const guruPublicPaths = ['/guru', '/guru/buat-akun', '/guru/lupa-password', '/guru/reset-password'];
    const isGuruPublic = guruPublicPaths.includes(to.path);
    if (to.path === '/guru' && isLoggedIn && isGuruRole) {
      next('/guru/dashboard');
      return;
    }
    if (!isGuruPublic && !isLoggedIn) {
      next('/guru');
      return;
    }
    if (!isGuruPublic && !isGuruRole) {
      next('/guru');
      return;
    }
    next();
    return;
  }

  const toAsrama = to.path.startsWith('/asrama');
  const isAsramaStaff = authStore.isAsramaStaff;

  if (toAsrama) {
    if (to.path === '/asrama' && isLoggedIn && isAsramaStaff) {
      next('/asrama/dashboard');
      return;
    }
    if (to.path !== '/asrama' && !isLoggedIn) {
      next('/asrama');
      return;
    }
    if (to.path !== '/asrama' && !isAsramaStaff) {
      next('/asrama');
      return;
    }
    next();
    return;
  }

  const toKafetaria = to.path.startsWith('/kafetaria');
  const isKafetariaStaff = authStore.isKafetariaStaff;

  if (toKafetaria) {
    if (to.path === '/kafetaria' && isLoggedIn && isKafetariaStaff) {
      next('/kafetaria/dashboard');
      return;
    }
    if (to.path !== '/kafetaria' && !isLoggedIn) {
      next('/kafetaria');
      return;
    }
    if (to.path !== '/kafetaria' && !isKafetariaStaff) {
      next('/kafetaria');
      return;
    }
    next();
    return;
  }

  if (toAdministrasi) {
    if (to.path === '/administrasi/login' && isLoggedIn && isAdministrasiRole) {
      next('/administrasi/dashboard');
      return;
    }
    if (to.path !== '/administrasi/login' && !isLoggedIn) {
      next('/administrasi/login');
      return;
    }
    if (to.path !== '/administrasi/login' && !isAdministrasiRole) {
      next('/administrasi/login');
      return;
    }
    next();
    return;
  }

  if (to.path === '/siswa' && isLoggedIn) {
    try {
      const jk = user.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
      next(`/siswa/${jk}/dashboard`);
      return;
    } catch {
      next();
      return;
    }
  }
  if (toSiswa && !isLoggedIn) {
    next('/siswa');
    return;
  }
  if (to.path === '/register' && isLoggedIn) {
    try {
      const jk = user.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
      next(`/siswa/${jk}/dashboard`);
      return;
    } catch {
      next();
      return;
    }
  }
  if (to.path === '/') {
    if (isLoggedIn) {
      if (isGuruRole) {
        next('/guru/dashboard');
        return;
      }
      if (isStaffRole) {
        next('/staff/dashboard');
        return;
      }
      if (isAsramaStaff) {
        next('/asrama/dashboard');
        return;
      }
      if (isKafetariaStaff) {
        next('/kafetaria/dashboard');
        return;
      }
      try {
        const jk = user.jenis_kelamin === 'perempuan' ? 'perempuan' : 'laki-laki';
        next(`/siswa/${jk}/dashboard`);
        return;
      } catch {}
    }
    next('/siswa');
    return;
  }
  next();
});

export default router;
