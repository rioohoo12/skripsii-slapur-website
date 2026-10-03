<template>
  <div class="flex h-screen bg-gray-50 font-sans text-gray-800">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col hidden md:flex z-20">
      
      <!-- Brand -->
      <div class="h-16 flex items-center justify-center border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Sistem Staff</h2>
      </div>
      
      <!-- Profile Card -->
      <div class="p-4">
        <div class="bg-blue-50 rounded-lg p-4 flex flex-col items-center text-center">
          <div class="w-12 h-12 bg-blue-200 text-blue-700 rounded-full flex items-center justify-center font-bold text-lg mb-2">
            {{ getInitials(authStore.user?.name || 'Staff') }}
          </div>
          <p class="font-semibold text-gray-800 text-sm">{{ authStore.user?.name || 'Staff Administrasi' }}</p>
          <p class="text-xs text-blue-600">{{ authStore.user?.email || 'staff@sekolah.com' }}</p>
        </div>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-2 px-3 space-y-1">
        <router-link to="/staff/dashboard" class="nav-item group" active-class="nav-active">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Dashboard
        </router-link>
        
        <router-link to="/staff/pendaftaran" class="nav-item group" active-class="nav-active">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          Pendaftaran
        </router-link>
        
        <router-link to="/staff/dokumen" class="nav-item group" active-class="nav-active">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          Dokumen
        </router-link>
        
        <router-link to="/staff/pembayaran" class="nav-item group" active-class="nav-active">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
          Pembayaran
        </router-link>
      </nav>
      
      <!-- Footer Logout -->
      <div class="p-4 border-t border-gray-200">
        <button @click="handleLogout" class="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      
      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <button class="md:hidden text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
          <h1 class="text-xl font-bold text-gray-800 hidden sm:block">
            {{ $route.name?.replace('Administrasi', '') || 'Dashboard' }}
          </h1>
        </div>
        
        <div class="flex items-center gap-6 text-sm">
          <div class="hidden sm:flex items-center gap-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            {{ authStore.user?.email || 'staff@sekolah.com' }}
          </div>
          <button @click="handleLogout" class="text-gray-600 hover:text-red-600 font-medium hidden sm:block">
            Logout
          </button>
        </div>
      </header>
      
      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <router-view></router-view>
      </div>

      <!-- Chatbot Icon (Bottom Right) -->
      <div class="fixed bottom-6 right-6 z-50">
        <button class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-transform hover:scale-105">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAdministrasiAuthStore } from '@/stores/administrasiAuth';
import { useRouter } from 'vue-router';

const authStore = useAdministrasiAuthStore();
const router = useRouter();

const getInitials = (name) => {
  return name.substring(0, 1).toUpperCase();
};

const handleLogout = () => {
  authStore.logout();
  router.push('/staff/login');
};
</script>

<style scoped>
.nav-item {
  @apply flex items-center px-4 py-2.5 rounded-lg text-gray-600 font-medium hover:bg-gray-100 transition-colors;
}
.nav-active {
  @apply bg-blue-50 text-blue-700;
}
</style>
