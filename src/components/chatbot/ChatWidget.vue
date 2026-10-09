<template>
  <div 
    class="slapur-widget-container" 
    style="position: fixed; bottom: 6rem; right: 1.25rem; z-index: 9999;"
  >
    <!-- Floating Trigger Button (Bottom-Right Footer Corner) -->
    <button 
      v-if="!store.isOpen" 
      @click="store.toggleChat()"
      class="trigger-floating-btn"
      aria-label="Buka Asisten SLAPUR"
    >
      <div class="trigger-avatar">🤖</div>
      <div class="trigger-info">
        <span class="trigger-title">Asisten SLAPUR</span>
        <span class="trigger-sub">● Berinteraksi Langsung</span>
      </div>
    </button>

    <!-- Chat Window Modal Window -->
    <div 
      v-else 
      class="chat-modal-window"
    >
      <!-- Chat Header -->
      <div class="chat-header">
        <div class="bot-info">
          <div class="bot-avatar">🤖</div>
          <div>
            <h3>Asisten SLAPUR</h3>
            <span class="online-status">● Berinteraksi Langsung</span>
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
import { ref, onMounted, watch, nextTick } from 'vue';
import { useChatbotStore } from '@/stores/chatbot';
import { useAuthStore } from '@/stores/auth';
import ChatMessage from './ChatMessage.vue';
import ActionCard from './ActionCard.vue';
import TypingIndicator from './TypingIndicator.vue';
import ChatInput from './ChatInput.vue';

const store = useChatbotStore();
const authStore = useAuthStore();

const messagesContainer = ref(null);
const chatInputRef = ref(null);

async function scrollToBottom() {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function detectRole() {
  if (authStore?.user?.role) {
    store.setRole(authStore.user.role);
  } else if (window.location.pathname.includes('/guru')) {
    store.setRole('guru');
  } else if (window.location.pathname.includes('/staff') || window.location.pathname.includes('/administrasi')) {
    store.setRole('staff');
  } else if (window.location.pathname.includes('/admin')) {
    store.setRole('admin');
  } else if (window.location.pathname.includes('/siswa')) {
    store.setRole('murid');
  } else {
    store.setRole('umum');
  }
}

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
</style>
