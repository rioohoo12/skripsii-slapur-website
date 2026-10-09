<template>
  <div class="chat-input-bar">
    <form @submit.prevent="handleSubmit" class="input-form">
      <input 
        v-model="inputMessage" 
        type="text" 
        maxlength="500"
        placeholder="Jawab pertanyaan bot di sini (misal: Nama Lengkap, Pendaftaran, SPP, dll)..." 
        :disabled="isLoading"
        ref="inputRef"
      />

      <button 
        type="submit" 
        class="send-btn"
        :disabled="!inputMessage.trim() || isLoading"
      >
        Kirim
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['send']);
const inputMessage = ref('');
const inputRef = ref(null);

function handleSubmit() {
  if (!inputMessage.value.trim() || props.isLoading) return;
  emit('send', inputMessage.value.trim());
  inputMessage.value = '';
}

function focusInput() {
  inputRef.value?.focus();
}

defineExpose({ focusInput });
</script>

<style scoped>
.chat-input-bar {
  padding: 0.85rem 1.25rem;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
}

.input-form {
  display: flex;
  gap: 0.65rem;
  align-items: center;
}

.input-form input {
  flex: 1;
  padding: 0.65rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-size: 0.85rem;
  outline: none;
  transition: border-color 0.2s;
  color: #1e293b;
  background: #ffffff;
}

.input-form input:focus {
  border-color: #0d6e59;
}

.send-btn {
  background: #62a398;
  color: #ffffff;
  border: none;
  padding: 0.65rem 1.35rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
  white-space: nowrap;
}

.send-btn:hover:not(:disabled) {
  background: #0d6e59;
}

.send-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
</style>
