<template>
  <div class="fixed bottom-4 right-4 z-50">
    <!-- Chat Button -->
    <button 
      v-if="!isOpen" 
      @click="toggleChat"
      class="bg-blue-600 text-white rounded-full p-4 shadow-lg hover:bg-blue-700 transition-colors"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
      </svg>
    </button>

    <!-- Chat Window -->
    <div 
      v-else 
      class="bg-white rounded-lg shadow-xl w-80 sm:w-96 flex flex-col overflow-hidden border border-gray-200"
      style="height: 500px;"
    >
      <!-- Header -->
      <div class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h3 class="font-semibold">Asisten Pendaftaran</h3>
        <button @click="toggleChat" class="text-white hover:text-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Messages -->
      <div class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-3" ref="messagesContainer">
        <div 
          v-for="(msg, index) in messages" 
          :key="index"
          :class="['max-w-[80%] rounded-lg p-3 text-sm', msg.role === 'user' ? 'bg-blue-100 text-blue-900 self-end rounded-tr-none' : 'bg-white border border-gray-200 text-gray-800 self-start rounded-tl-none']"
        >
          {{ msg.message }}
        </div>
      </div>

      <!-- Quick Reply Buttons -->
      <div v-if="!isLoading && messages.length > 0" class="px-3 pb-2 pt-2 bg-gray-50 flex gap-2 overflow-x-auto whitespace-nowrap" style="scrollbar-width: none;">
        <button @click="sendQuickReply('Info Pendaftaran')" class="text-xs bg-white text-blue-600 border border-blue-200 rounded-full px-3 py-1.5 hover:bg-blue-50 transition-colors shadow-sm">
          Info Pendaftaran
        </button>
        <button @click="sendQuickReply('Cara Daftar')" class="text-xs bg-white text-blue-600 border border-blue-200 rounded-full px-3 py-1.5 hover:bg-blue-50 transition-colors shadow-sm">
          Cara Daftar
        </button>
        <button @click="sendQuickReply('Bicara dengan Staf')" class="text-xs bg-white text-blue-600 border border-blue-200 rounded-full px-3 py-1.5 hover:bg-blue-50 transition-colors shadow-sm">
          Bicara dg Staf
        </button>
      </div>

      <!-- Input Form -->
      <div class="p-3 bg-white border-t border-gray-200">
        <form @submit.prevent="sendMessage" class="flex gap-2">
          <input 
            v-model="newMessage" 
            type="text" 
            placeholder="Ketik pesan..." 
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :disabled="isLoading"
          />
          <button 
            type="submit" 
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
            :disabled="!newMessage.trim() || isLoading"
          >
            Kirim
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const isLoading = ref(false);
const sessionId = ref(localStorage.getItem('chat_session_id'));
const messagesContainer = ref(null);

const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value && messages.value.length === 0) {
    initChat();
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const initChat = async () => {
  try {
    if (!sessionId.value) {
      const response = await axios.post('/api/v2/chat/session');
      sessionId.value = response.data.data.session_id;
      localStorage.setItem('chat_session_id', sessionId.value);
      
      messages.value.push({
        role: 'assistant',
        message: 'Halo! Saya asisten pendaftaran. Ada yang bisa saya bantu terkait pendaftaran siswa baru?'
      });
    } else {
      const response = await axios.get(`/api/v2/chat/session/${sessionId.value}/history`);
      messages.value = response.data.messages;
      
      if (messages.value.length === 0) {
        messages.value.push({
          role: 'assistant',
          message: 'Halo! Saya asisten pendaftaran. Ada yang bisa saya bantu terkait pendaftaran siswa baru?'
        });
      }
    }
    scrollToBottom();
  } catch (error) {
    console.error('Failed to init chat:', error);
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !sessionId.value) return;
  
  const text = newMessage.value;
  newMessage.value = '';
  
  messages.value.push({
    role: 'user',
    message: text
  });
  scrollToBottom();
  isLoading.value = true;
  
  try {
    const response = await axios.post('/api/chatbot/message', {
      session_id: sessionId.value,
      message: text
    });
    
    messages.value.push({
      role: 'assistant',
      message: response.data.reply
    });
    
    if (response.data.action) {
      window.dispatchEvent(new CustomEvent('chatbot-action', { detail: response.data.action }));
    }
    
    scrollToBottom();
  } catch (error) {
    console.error('Failed to send message:', error);
    messages.value.push({
      role: 'assistant',
      message: 'Maaf, terjadi kesalahan saat memproses pesan Anda.'
    });
    scrollToBottom();
  } finally {
    isLoading.value = false;
  }
};

const sendQuickReply = (text) => {
  newMessage.value = text;
  sendMessage();
};

onMounted(() => {
  // Setup default axios base URL if not already configured in your app
  axios.defaults.baseURL = 'http://127.0.0.1:8000';
  
  window.addEventListener('open-chat', (e) => {
    isOpen.value = true;
    if (messages.value.length === 0) {
      initChat();
    }
    if (e.detail && typeof e.detail === 'string') {
       newMessage.value = e.detail;
       sendMessage();
    }
  });
});
</script>
