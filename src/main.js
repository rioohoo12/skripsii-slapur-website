import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

import axios from 'axios';
import { useAdministrasiAuthStore } from './stores/administrasiAuth';

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      if (window.location.pathname.startsWith('/administrasi')) {
         const adminStore = useAdministrasiAuthStore();
         adminStore.logout();
         router.push('/administrasi/login');
      }
    }
    return Promise.reject(error);
  }
);

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');
