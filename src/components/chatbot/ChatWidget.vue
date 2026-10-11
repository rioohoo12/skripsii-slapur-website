<template>
  <div 
    class="slapur-widget-container" 
    :class="{ 'is-open': store.isOpen }"
  >
    <!-- Floating Trigger Button (Bottom-Right Corner) -->
    <button 
      v-if="!store.isOpen" 
      @click="store.toggleChat()"
      class="trigger-floating-btn"
      :class="roleInfo.theme"
      :aria-label="roleInfo.title"
    >
      <div class="trigger-avatar">{{ roleInfo.avatar }}</div>
      <div class="trigger-info">
        <span class="trigger-title">{{ roleInfo.title }}</span>
        <span class="trigger-sub">{{ roleInfo.subtitle }}</span>
      </div>
    </button>

    <!-- Chat Window Modal Window -->
    <div 
      v-else 
      class="chat-modal-window"
    >
      <!-- Chat Header -->
      <div class="chat-header" :class="roleInfo.theme">
        <div class="bot-info">
          <div class="bot-avatar">{{ roleInfo.avatar }}</div>
          <div>
            <h3>{{ roleInfo.title }}</h3>
            <span class="online-status">{{ roleInfo.subtitle }}</span>
          </div>
        </div>

        <div class="header-actions">
          <button @click="store.clearHistory()" class="action-header-btn reset-btn" title="Mulai Ulang Chat">
            🔄 Mulai Ulang
          </button>
          <button @click="store.toggleChat()" class="action-header-btn exit-btn" title="Keluar Chat">
            ✕ Keluar
          </button>
        </div>
      </div>

      <!-- Error Alert Banner -->
      <div v-if="store.errorMsg" class="error-banner">
        <span>{{ store.errorMsg }}</span>
        <button @click="store.errorMsg = ''" class="close-error">&times;</button>
      </div>

      <!-- Messages Scroll Area -->
      <div 
        ref="messagesContainer"
        class="chat-messages-area"
      >
        <ChatMessage 
          v-for="(msg, index) in store.messages" 
          :key="index"
          :message="msg"
        />

        <!-- Action Card (if active) -->
        <ActionCard 
          v-if="store.activeAction"
          :type="store.activeAction"
          :slots-data="store.slots"
          @action-click="handleActionCardClick"
        />

        <!-- Typing Indicator -->
        <TypingIndicator v-if="store.isLoading" />
      </div>

      <!-- Quick Reply Pill Buttons per Role -->
      <div 
        v-if="!store.isLoading && store.quickReplies.length > 0" 
        class="quick-reply-bar"
      >
        <button 
          v-for="(reply, idx) in store.quickReplies" 
          :key="idx"
          @click="sendQuickReply(reply)"
          class="opt-btn"
        >
          💡 {{ reply }}
        </button>
      </div>

      <!-- Chat Input Component -->
      <ChatInput 
        :is-loading="store.isLoading" 
        @send="handleSend"
        ref="chatInputRef"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useChatbotStore } from '@/stores/chatbot';
import { useAuthStore } from '@/stores/auth';
import ChatMessage from './ChatMessage.vue';
import ActionCard from './ActionCard.vue';
import TypingIndicator from './TypingIndicator.vue';
import ChatInput from './ChatInput.vue';

const store = useChatbotStore();
const authStore = useAuthStore();
const route = useRoute();

const messagesContainer = ref(null);
const chatInputRef = ref(null);

const roleInfo = computed(() => {
  const role = (store.userRole || 'umum').toLowerCase();
  switch (role) {
    case 'staff_administrasi':
    case 'administrasi':
    case 'staff':
      return {
        title: 'Asisten Administrasi',
        subtitle: '● Layanan Staf & Info Siswa',
        avatar: '👨‍💼',
        theme: 'theme-staff',
      };
    case 'staff_asrama_putra':
    case 'asrama_putra':
      return {
        title: 'Asisten Asrama Putra',
        subtitle: '● Gedung A-C & Total Murid',
        avatar: '🏢',
        theme: 'theme-asrama-putra',
      };
    case 'staff_asrama_putri':
    case 'asrama_putri':
      return {
        title: 'Asisten Asrama Putri',
        subtitle: '● Gedung D-F & Total Murid',
        avatar: '🌸',
        theme: 'theme-asrama-putri',
      };
    case 'staff_asrama':
      return {
        title: 'Asisten Asrama',
        subtitle: '● Layanan Kamar & Santri',
        avatar: '🏰',
        theme: 'theme-asrama',
      };
    case 'staff_kafetaria':
    case 'kafetaria':
      return {
        title: 'Asisten Kafetaria',
        subtitle: '● Menu Makanan & Dining',
        avatar: '🍲',
        theme: 'theme-kafetaria',
      };
    case 'guru':
    case 'teacher':
      return {
        title: 'Asisten Guru',
        subtitle: '● Jadwal & Nilai Kelas',
        avatar: '👨‍🏫',
        theme: 'theme-guru',
      };
    case 'murid':
    case 'student':
    case 'siswa':
      return {
        title: 'Asisten Siswa',
        subtitle: '● Panduan & Tagihan Siswa',
        avatar: '🎓',
        theme: 'theme-siswa',
      };
    case 'admin':
      return {
        title: 'Asisten Admin',
        subtitle: '● Kelola Sistem SLAPUR',
        avatar: '⚡',
        theme: 'theme-admin',
      };
    default:
      return {
        title: 'Asisten SLAPUR',
        subtitle: '● Berinteraksi Langsung',
        avatar: '🤖',
        theme: 'theme-default',
      };
  }
});

async function scrollToBottom() {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function detectRole() {
  const path = window.location.pathname.toLowerCase();
  const user = authStore?.user;

  if (path.includes('/asramaputra') || (user?.role === 'staff_asrama' && user?.jenis_kelamin === 'laki-laki') || (user?.role === 'staff_asrama' && user?.email?.includes('putra'))) {
    store.setRole('staff_asrama_putra');
  } else if (path.includes('/asramaputri') || (user?.role === 'staff_asrama' && user?.jenis_kelamin === 'perempuan') || (user?.role === 'staff_asrama' && user?.email?.includes('putri'))) {
    store.setRole('staff_asrama_putri');
  } else if (user?.role === 'staff_asrama') {
    store.setRole('staff_asrama');
  } else if (path.includes('/kafetaria') || user?.role === 'staff_kafetaria' || user?.role === 'kafetaria') {
    store.setRole('staff_kafetaria');
  } else if (path.includes('/administrasi') || user?.role === 'staff') {
    store.setRole('staff_administrasi');
  } else if (path.includes('/guru') || user?.role === 'guru') {
    store.setRole('guru');
  } else if (path.includes('/siswa') || user?.role === 'siswa' || user?.role === 'student') {
    store.setRole('siswa');
  } else if (path.includes('/admin') || user?.role === 'admin' || user?.role === 'super_admin') {
    store.setRole('admin');
  } else if (user?.role) {
    store.setRole(user.role);
  } else {
    store.setRole('umum');
  }
}

watch(() => [route?.path, authStore?.user], () => {
  detectRole();
});

async function handleSend(text) {
  await store.sendMessage(text);
  scrollToBottom();
}

function sendQuickReply(replyText) {
  handleSend(replyText);
}

function handleActionCardClick(event) {
  if (event.action === 'pembayaran') {
    store.confirmAction('pembayaran', store.slots);
  } else if (event.action === 'pendaftaran') {
    store.confirmAction('pendaftaran', store.slots);
  }
}

watch(() => store.messages.length, () => {
  scrollToBottom();
});

onMounted(() => {
  detectRole();

  window.addEventListener('open-chat', (e) => {
    store.isOpen = true;
    if (store.messages.length === 0) {
      store.initChat();
    }
    if (e.detail && typeof e.detail === 'string') {
      handleSend(e.detail);
    }
  });
});
</script>

<style scoped>
.slapur-widget-container {
  font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
  user-select: none;
}

.slapur-widget-container {
  position: fixed;
  bottom: 2rem;
  right: 1.5rem;
  z-index: 9999;
}

/* Floating Trigger Button */
.trigger-floating-btn {
  background: #0d6e59;
  color: #ffffff;
  border: none;
  border-radius: 999px;
  padding: 0.75rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 10px 25px rgba(13, 110, 89, 0.35);
  cursor: pointer;
  transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
}

.trigger-floating-btn:hover {
  background: #0f766e;
  transform: translateY(-2px);
  box-shadow: 0 14px 30px rgba(13, 110, 89, 0.45);
}

.trigger-avatar {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.trigger-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.trigger-title {
  font-size: 0.85rem;
  font-weight: 700;
  line-height: 1.2;
}

.trigger-sub {
  font-size: 0.7rem;
  color: #5eead4;
}

/* Chat Modal Window */
.chat-modal-window {
  width: 420px;
  max-width: 92vw;
  height: min(82vh, 600px);
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Chat Header */
.chat-header {
  background: linear-gradient(135deg, #0d6e59, #115e59);
  color: #ffffff;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.bot-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.bot-avatar {
  width: 38px;
  height: 38px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.bot-info h3 {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  color: #ffffff;
}

.online-status {
  font-size: 0.75rem;
  color: #5eead4;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-header-btn {
  background: rgba(255, 255, 255, 0.18);
  border: none;
  color: #ffffff;
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.action-header-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.exit-btn {
  background: rgba(239, 68, 68, 0.4);
}

.exit-btn:hover {
  background: rgba(239, 68, 68, 0.7);
}

/* Error Banner */
.error-banner {
  background: #fef2f2;
  border-bottom: 1px solid #fecaca;
  color: #991b1b;
  padding: 0.5rem 1.25rem;
  font-size: 0.8rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-error {
  background: none;
  border: none;
  font-weight: bold;
  cursor: pointer;
  color: #991b1b;
}

/* Messages Scroll Area */
.chat-messages-area {
  flex: 1;
  padding: 1.25rem;
  overflow-y: auto;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.chat-messages-area::-webkit-scrollbar {
  width: 5px;
}

.chat-messages-area::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 999px;
}

/* Quick Reply Bar */
.quick-reply-bar {
  padding: 0.65rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
}

.quick-reply-bar::-webkit-scrollbar {
  display: none;
}

.opt-btn {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  shrink: 0;
}

.opt-btn:hover {
  background: #dcfce7;
}

/* Role Theme Specific Colors */
.trigger-floating-btn.theme-staff,
.chat-header.theme-staff {
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
}
.trigger-floating-btn.theme-asrama-putra,
.chat-header.theme-asrama-putra {
  background: linear-gradient(135deg, #065f46, #059669);
}
.trigger-floating-btn.theme-asrama-putri,
.chat-header.theme-asrama-putri {
  background: linear-gradient(135deg, #9d174d, #db2777);
}
.trigger-floating-btn.theme-kafetaria,
.chat-header.theme-kafetaria {
  background: linear-gradient(135deg, #c2410c, #ea580c);
}
.trigger-floating-btn.theme-guru,
.chat-header.theme-guru {
  background: linear-gradient(135deg, #4338ca, #6366f1);
}
.trigger-floating-btn.theme-siswa,
.chat-header.theme-siswa {
  background: linear-gradient(135deg, #0d6e59, #10b981);
}
.trigger-floating-btn.theme-admin,
.chat-header.theme-admin {
  background: linear-gradient(135deg, #0f172a, #334155);
}

@media (max-width: 640px) {
  .slapur-widget-container {
    bottom: 1.25rem;
    right: 1rem;
  }

  .slapur-widget-container.is-open {
    inset: 0;
    padding: 0.5rem;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: flex-end;
    justify-content: center;
  }

  .chat-modal-window {
    width: 100%;
    max-width: 100%;
    height: 88vh;
    border-radius: 20px 20px 12px 12px;
  }

  .trigger-floating-btn {
    padding: 0.65rem 1rem;
    gap: 0.6rem;
  }

  .trigger-sub {
    display: none;
  }
}
</style>
