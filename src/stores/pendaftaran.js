import { defineStore } from 'pinia';
import { request } from '@/api/auth';

export const usePendaftaranStore = defineStore('pendaftaran', {
  state: () => ({
    isFetched: false,
    isComplete: false,
    loading: false,
  }),
  actions: {
    async fetchStatus() {
      if (this.isFetched) return;
      this.loading = true;
      try {
        const res = await request('/pendaftaran/status');
        // Buka kunci akses menu-menu utama jika pembayaran pendaftaran sudah terverifikasi (Langkah 1 selesai)
        this.isComplete = res.steps[1].selesai;
        this.isFetched = true;
      } catch (e) {
        console.error('Failed to fetch pendaftaran status', e);
      } finally {
        this.loading = false;
      }
    },
    setComplete() {
      this.isComplete = true;
      this.isFetched = true;
    }
  }
});
