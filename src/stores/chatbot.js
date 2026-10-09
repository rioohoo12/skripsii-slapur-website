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
        case 'murid':
        case 'student':
        case 'siswa':
          return ['Cara Gunakan Website', 'Panduan Pendaftaran', 'Tagihan Saya', 'Jadwal Pelajaran', 'Bantuan Staf'];
        case 'guru':
        case 'teacher':
          return ['Jadwal Mengajar', 'Panduan Input Absensi', 'Panduan Input Nilai', 'Daftar Kelas'];
        case 'staff':
        case 'administrasi':
          return ['Ringkasan Siswa', 'Status Pembayaran Siswa', 'Ketersediaan Kamar', 'Panduan Verifikasi'];
        case 'admin':
          return ['Panduan Kelola Role', 'Ringkasan Monitoring', 'FAQ Sekolah'];
        case 'umum':
        default:
          return ['Cara Gunakan Website', 'Info Pendaftaran', 'Biaya & SPP', 'Aturan Asrama', 'Bicara dg Staf'];
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
            message: 'Halo! 👋 Saya Asisten SLAPUR. Saya siap membantu kamu memahami cara menggunakan website SLAPUR, navigasi menu, info pendaftaran, jadwal, hingga pembayaran SPP. Ada yang ingin kamu tanyakan?',
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
              message: 'Halo! 👋 Saya Asisten SLAPUR. Saya siap membantu kamu memahami cara menggunakan website SLAPUR, navigasi menu, info pendaftaran, jadwal, hingga pembayaran SPP. Ada yang ingin kamu tanyakan?',
            },
          ];
        }
      } catch (err) {
        console.error('Failed to load chat history:', err);
        this.messages = [
          {
            role: 'assistant',
            message: 'Halo! 👋 Saya Asisten SLAPUR. Ada yang bisa saya bantu terkait penggunaan website SLAPUR?',
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
