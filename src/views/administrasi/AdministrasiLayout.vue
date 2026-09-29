<template>
  <div class="administrasi-layout flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col">
      <div class="p-4 flex items-center gap-3 border-b border-blue-700">
        <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-blue-800 font-bold">
          A
        </div>
        <h2 class="font-bold text-lg">Administrasi</h2>
      </div>
      
      <nav class="flex-1 overflow-y-auto py-4">
        <router-link to="/administrasi/dashboard" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">📊</span> Dashboard
        </router-link>
        <router-link to="/administrasi/siswa" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">👨‍🎓</span> Data Siswa
        </router-link>
        <router-link to="/administrasi/dokumen" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">📄</span> Dokumen
        </router-link>
        <router-link to="/administrasi/pembayaran" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">💳</span> Pembayaran
        </router-link>
        <router-link to="/administrasi/tagihan" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">🧾</span> Tagihan
        </router-link>
        <router-link to="/administrasi/laporan" class="flex items-center px-6 py-3 hover:bg-blue-700" active-class="bg-blue-900 border-l-4 border-white">
          <span class="mr-3">📈</span> Laporan
        </router-link>
      </nav>
      
      <div class="p-4 border-t border-blue-700">
        <button @click="handleLogout" class="flex items-center w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white transition">
          <span class="mr-2">🚪</span> Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Topbar -->
      <header class="bg-white h-16 border-b border-gray-200 flex items-center justify-between px-6">
        <h1 class="text-xl font-semibold text-gray-800">
          {{ $route.name?.replace('Administrasi', '') || 'Dashboard' }}
        </h1>
        <div class="flex items-center gap-4">
          <div class="text-sm text-gray-600 font-medium">
            Halo, {{ authStore.user?.name || 'Staff Administrasi' }}
          </div>
          <img :src="`https://ui-avatars.com/api/?name=${authStore.user?.name || 'Staff'}&background=random`" alt="Avatar" class="w-10 h-10 rounded-full" />
        </div>
      </header>
      
      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAdministrasiAuthStore } from '@/stores/administrasiAuth';
import { useRouter } from 'vue-router';

const authStore = useAdministrasiAuthStore();
const router = useRouter();

const handleLogout = () => {
  authStore.logout();
  router.push('/administrasi/login');
};
</script>
