import { defineStore } from 'pinia';
import { authApi } from '@/api/auth';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),
  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
    isGuru: (state) => {
      const roles = ['guru', 'admin', 'super_admin'];
      return state.user && roles.includes(state.user.role);
    },
    isStaff: (state) => {
      const roles = ['staff', 'admin', 'super_admin'];
      return state.user && roles.includes(state.user.role);
    },
    isSiswa: (state) => {
      return state.user && state.user.role === 'siswa';
    }
  },
  actions: {
    init() {
      try {
        const storedToken = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');
        if (storedToken && storedUser) {
          this.token = storedToken;
          this.user = JSON.parse(storedUser);
          authApi.setToken(storedToken);
        }
      } catch (e) {
        this.clearAuth();
      }
    },
    setAuth(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem('auth_token', token);
      localStorage.setItem('user', JSON.stringify(user));
      authApi.setToken(token);
    },
    clearAuth() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      authApi.setToken(null);
    },
    async logout() {
      try {
        await authApi.logout();
      } catch (e) {
        console.error('Logout failed on server', e);
      } finally {
        this.clearAuth();
      }
    }
  }
});
