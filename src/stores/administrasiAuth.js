import { defineStore } from 'pinia';
import axios from 'axios';

export const useAdministrasiAuthStore = defineStore('administrasiAuth', {
  state: () => ({
    token: localStorage.getItem('administrasi_token') || null,
    user: JSON.parse(localStorage.getItem('administrasi_user')) || null,
  }),
  getters: {
    isLoggedIn: (state) => !!state.token,
    isStaffAdministrasi: (state) => state.user?.role === 'staff' || state.user?.role === 'admin' || state.user?.role === 'super_admin',
  },
  actions: {
    setAuth(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem('administrasi_token', token);
      localStorage.setItem('administrasi_user', JSON.stringify(user));
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('administrasi_token');
      localStorage.removeItem('administrasi_user');
      delete axios.defaults.headers.common['Authorization'];
    },
    init() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    }
  }
});
