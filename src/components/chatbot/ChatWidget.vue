<template>
  <div class="fixed bottom-4 right-4 z-50 font-sans">
    <!-- Floating Toggle Button -->
    <button 
      v-if="!store.isOpen" 
      @click="store.toggleChat()"
      class="bg-gradient-to-r from-teal-700 to-teal-800 text-white rounded-full p-4 shadow-xl hover:shadow-teal-900/30 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center gap-2 group border border-teal-500/30"
      aria-label="Buka Chatbot"
    >
      <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 border-2 border-teal-800 rounded-full"></span>
      </div>
      <span class="hidden sm:inline text-xs font-bold tracking-wide pr-1">Asisten SLAPUR</span>
    </button>

    <!-- Chat Modal Window -->
    <div 
      v-else 
      class="bg-slate-50 rounded-2xl shadow-2xl w-[92vw] sm:w-[400px] flex flex-col overflow-hidden border border-slate-200/80 transition-all duration-300"
      style="height: min(82vh, 580px);"
    >
      <!-- Header Bar -->
      <div class="bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-800 text-white p-3.5 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-white/20 border border-white/30 flex items-center justify-center font-bold text-sm text-emerald-200">
            🤖
          </div>
          <div>
            <h3 class="font-bold text-xs sm:text-sm tracking-wide leading-tight">Asisten Virtual SLAPUR</h3>
            <div class="flex items-center gap-1.5 text-[10px] text-teal-100">
              <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
              <span class="capitalize font-medium">Role: {{ store.userRole }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-1">
          <!-- Clear Chat History -->
          <button 
            @click="store.clearHistory()" 
            class="p-1.5 hover:bg-white/10 rounded-lg text-teal-100 hover:text-white transition-colors"
            title="Bersihkan Percakapan"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>

          <!-- Close Modal -->
          <button 
            @click="store.toggleChat()" 
            class="p-1.5 hover:bg-white/10 rounded-lg text-teal-100 hover:text-white transition-colors"
            title="Tutup Chat"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Error Alert Banner -->
      <div v-if="store.errorMsg" class="bg-rose-100 border-b border-rose-200 text-rose-800 px-3 py-2 text-xs flex items-center justify-between">
        <span class="truncate">{{ store.errorMsg }}</span>
        <button @click="store.errorMsg = ''" class="font-bold ml-2 hover:text-rose-900">&times;</button>
      </div>

      <!-- Messages Body Scroll Container -->
      <div 
        ref="messagesContainer"
        class="flex-1 p-3.5 overflow-y-auto flex flex-col gap-3 scroll-smooth"
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
        class="px-3 py-2 bg-white border-t border-slate-100 flex gap-1.5 overflow-x-auto no-scrollbar"
      >
        <button 
          v-for="(reply, idx) in store.quickReplies" 
          :key="idx"
          @click="sendQuickReply(reply)"
          class="text-[11px] font-semibold bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/70 rounded-full px-3 py-1 shrink-0 transition-colors shadow-xs"
        >
          {{ reply }}
        </button>
      </div>

      <!-- Chat Input Form Component -->
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
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
