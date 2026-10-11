import { defineStore } from 'pinia';
import { chatbotApi } from '@/api/chatbot';

export const useChatbotStore = defineStore('chatbot', {
  state: () => ({
    isOpen: false,
    messages: [],
    sessionId: localStorage.getItem('chat_session_id') || '',
    isLoading: false,
    errorMsg: '',
    slots: {},
    userRole: 'umum',
    activeAction: null,
  }),

  getters: {
    quickReplies: (state) => {
      const role = (state.userRole || 'umum').toLowerCase();
      switch (role) {
        case 'staff_administrasi':
        case 'administrasi':
        case 'staff':
          return [
            'Panduan Menu Administrasi',
            'Cari Info Siswa',
            'Ringkasan Pendaftar & Siswa',
            'Berkas Menunggu Verifikasi',
            'Antrean Pembayaran Masuk'
          ];
        case 'staff_asrama_putra':
        case 'asrama_putra':
          return [
            'Panduan Menu Asrama Putra',
            'Total Murid Asrama Putra',
            'Ketersediaan Kamar Putra',
            'Antrean Persetujuan Kamar',
            'Panduan Mutasi Kamar'
          ];
        case 'staff_asrama_putri':
        case 'asrama_putri':
          return [
            'Panduan Menu Asrama Putri',
            'Total Murid Asrama Putri',
            'Ketersediaan Kamar Putri',
            'Antrean Persetujuan Kamar',
            'Panduan Mutasi Kamar'
          ];
        case 'staff_asrama':
          return [
            'Panduan Menu Asrama Putra',
            'Total Murid di Asrama',
            'Ketersediaan Kamar Asrama',
            'Antrean Persetujuan Kamar',
            'Panduan Mutasi Kamar'
          ];
        case 'staff_kafetaria':
        case 'kafetaria':
          return [
            'Panduan Menu Kafetaria',
            'Menu Makanan Hari Ini',
            'Rekap Siswa Makan Hari Ini',
            'Panduan Scan Kartu Makan',
            'Input Menu Makanan Baru'
          ];
        case 'guru':
        case 'teacher':
          return [
            'Jadwal Mengajar',
            'Panduan Input Absensi',
            'Panduan Input Nilai',
            'Daftar Kelas'
          ];
        case 'murid':
        case 'student':
        case 'siswa':
          return [
            'Panduan Menu Siswa',
            'Tagihan & Status Bayar',
            'Jadwal Pelajaran',
            'Info Kamar Asrama',
            'Bantuan Staf'
          ];
        case 'admin':
          return [
            'Panduan Kelola Role',
            'Ringkasan Monitoring',
            'FAQ Sekolah'
          ];
        case 'umum':
        default:
          return [
            'Cara Gunakan Website',
            'Info Pendaftaran',
            'Biaya & SPP',
            'Aturan Asrama',
            'Bicara dg Staf'
          ];
      }
    },

    welcomeMessage: (state) => {
      const role = (state.userRole || 'umum').toLowerCase();
      switch (role) {
        case 'staff_administrasi':
        case 'administrasi':
        case 'staff':
          return 'Halo! 👨‍💼 Saya Asisten Administrasi SLAPUR. Saya siap memandu fungsi setiap menu administrasi dan menyajikan informasi data siswa lengkap dari database sekolah. Ada yang ingin dicari atau ditanyakan?';
        case 'staff_asrama_putra':
        case 'asrama_putra':
          return 'Halo! 🏢 Saya Asisten Asrama Putra SLAPUR. Saya siap memandu fungsi menu asrama putra, memberikan informasi total murid penghuni asrama putra, ketersediaan kamar Gedung A-C, dan persetujuan kamar.';
        case 'staff_asrama_putri':
        case 'asrama_putri':
          return 'Halo! 🌸 Saya Asisten Asrama Putri SLAPUR. Saya siap memandu fungsi menu asrama putri, memberikan informasi total murid penghuni asrama putri, ketersediaan kamar Gedung D-F, dan persetujuan kamar.';
        case 'staff_asrama':
          return 'Halo! 🏰 Saya Asisten Asrama SLAPUR. Saya siap memandu fungsi menu asrama serta memberikan informasi total murid di asrama putra maupun asrama putri.';
        case 'staff_kafetaria':
        case 'kafetaria':
          return 'Halo! 🍲 Saya Asisten Kafetaria SLAPUR. Saya siap memandu menu operasional kafetaria, jadwal menu makan hari ini, rekapitulasi siswa yang sudah makan, dan panduan scan kartu makan santri.';
        case 'guru':
        case 'teacher':
          return 'Halo Bapak/Ibu Guru! 👨‍🏫 Saya Asisten Guru SLAPUR. Saya siap memandu jadwal mengajar, penginputan absensi kelas, dan pengisian nilai siswa.';
        case 'murid':
        case 'student':
        case 'siswa':
          return 'Halo! 👋 Saya Asisten Siswa SLAPUR. Saya siap membantu kamu memahami navigasi menu, info pendaftaran, jadwal pelajaran, kamar asrama, hingga status tagihan pembayaranmu.';
        default:
          return 'Halo! 👋 Saya Asisten SLAPUR. Saya siap membantu Anda memahami cara menggunakan website SLAPUR, navigasi menu, info pendaftaran, jadwal, hingga pembayaran SPP. Ada yang ingin kamu tanyakan?';
      }
    },
  },

  actions: {
    setRole(role) {
      if (role) {
        this.userRole = role;
      }
    },

    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen && this.messages.length === 0) {
        this.initChat();
      }
    },

    async initChat() {
      this.errorMsg = '';
      if (!this.sessionId) {
        this.messages = [
          {
            role: 'assistant',
            message: this.welcomeMessage,
          },
        ];
        return;
      }

      this.isLoading = true;
      try {
        const res = await chatbotApi.getHistory(this.sessionId);
        if (res.messages && res.messages.length > 0) {
          this.messages = res.messages.map((m) => ({
            role: m.role || (m.sender_type === 'user' ? 'user' : 'assistant'),
            message: m.message || m.respons || m.pesan || '',
            intent: m.intent,
            source: m.source || m.sumber,
          }));
        } else {
          this.messages = [
            {
              role: 'assistant',
              message: this.welcomeMessage,
            },
          ];
        }
      } catch (err) {
        console.error('Failed to load chat history:', err);
        this.messages = [
          {
            role: 'assistant',
            message: this.welcomeMessage,
          },
        ];
      } finally {
        this.isLoading = false;
      }
    },

    async sendMessage(text) {
      if (!text || !text.trim()) return;
      const cleanText = text.trim();

      this.errorMsg = '';
      this.messages.push({
        role: 'user',
        message: cleanText,
      });

      this.isLoading = true;

      try {
        const res = await chatbotApi.sendMessage(this.sessionId, cleanText);

        if (res.session_id) {
          this.sessionId = res.session_id;
          localStorage.setItem('chat_session_id', res.session_id);
        }

        const replyMessage = res.reply?.message || res.reply || 'Terjadi kesalahan pada respon bot.';
        
        this.messages.push({
          role: 'assistant',
          message: replyMessage,
          intent: res.intent,
          source: res.source,
        });

        if (res.slots) {
          this.slots = res.slots;
          window.dispatchEvent(new CustomEvent('chatbot-slots', { detail: res.slots }));
        }

        if (res.action) {
          this.activeAction = res.action;
          window.dispatchEvent(new CustomEvent('chatbot-action', { detail: res.action }));
        }

      } catch (err) {
        console.error('Chat error:', err);
        const errorText = err.data?.message || err.message || 'Maaf, gagal menghubungkan ke server chatbot.';
        this.errorMsg = errorText;
        this.messages.push({
          role: 'assistant',
          message: `⚠️ ${errorText}`,
          isError: true,
        });
      } finally {
        this.isLoading = false;
      }
    },

    async confirmAction(actionType, payload = {}) {
      this.isLoading = true;
      this.errorMsg = '';
      try {
        const res = await chatbotApi.confirmAction(this.sessionId, actionType, payload);
        this.messages.push({
          role: 'assistant',
          message: res.message || 'Aksi berhasil dikonfirmasi.',
          intent: res.action,
          source: 'action',
        });
        if (res.data) {
          window.dispatchEvent(new CustomEvent('chatbot-action-confirmed', { detail: res }));
        }
      } catch (err) {
        console.error('Confirm action error:', err);
        this.errorMsg = err.message || 'Gagal mengonfirmasi aksi.';
      } finally {
        this.isLoading = false;
      }
    },

    async clearHistory() {
      if (!this.sessionId) return;
      this.isLoading = true;
      try {
        await chatbotApi.clearChat(this.sessionId);
        this.messages = [
          {
            role: 'assistant',
            message: 'Percakapan berhasil dibersihkan. Ada yang bisa saya bantu kembali?',
          },
        ];
        this.slots = {};
        this.activeAction = null;
      } catch (err) {
        console.error('Clear chat error:', err);
      } finally {
        this.isLoading = false;
      }
    },
  },
});
