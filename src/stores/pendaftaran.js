import { defineStore } from 'pinia';
import { request } from '@/api/auth';

export const usePendaftaranStore = defineStore('pendaftaran', {
  state: () => ({
    isFetched: false,
    isComplete: false,
    loading: false,
    tagihanSummary: null,
  }),
  actions: {
    async fetchStatus(force = false) {
      if (this.isFetched && !force) return;
      this.loading = true;
      try {
        const [resStatus, resTagihan] = await Promise.allSettled([
          request('/pendaftaran/status'),
          request('/siswa/tagihan')
        ]);

        if (resStatus.status === 'fulfilled' && resStatus.value) {
          const res = resStatus.value;
          // Buka kunci akses menu-menu utama jika pembayaran terverifikasi atau pendaftaran selesai
          this.isComplete = Boolean(res?.steps?.[1]?.selesai || res?.steps?.[5]?.selesai);
        }

        if (resTagihan.status === 'fulfilled' && resTagihan.value?.summary) {
          this.tagihanSummary = resTagihan.value.summary;
        }

        this.isFetched = true;
      } catch (e) {
        console.error('Failed to fetch pendaftaran status', e);
      } finally {
        this.loading = false;
      }
    },
    async fetchTagihan() {
      try {
        const res = await request('/siswa/tagihan');
        if (res?.summary) {
          this.tagihanSummary = res.summary;
        }
      } catch (e) {
        console.error('Failed to fetch tagihan summary', e);
      }
    },
    setComplete() {
      this.isComplete = true;
      this.isFetched = true;
    },
    reset() {
      this.isFetched = false;
      this.isComplete = false;
      this.loading = false;
      this.tagihanSummary = null;
    }
  }
});
