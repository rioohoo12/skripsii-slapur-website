<template>
  <div class="p-3 bg-white border-t border-slate-200 flex flex-col gap-1.5">
    <form @submit.prevent="handleSubmit" class="flex items-center gap-2">
      <input 
        v-model="inputMessage" 
        type="text" 
        maxlength="500"
        placeholder="Ketik pertanyaan Anda di sini..." 
        class="flex-1 px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all disabled:opacity-60"
        :disabled="isLoading"
        ref="inputRef"
      />

      <button 
        type="submit" 
        class="bg-teal-700 hover:bg-teal-800 active:scale-95 text-white p-2.5 rounded-xl transition-all disabled:opacity-40 disabled:hover:bg-teal-700 disabled:active:scale-100 shadow-xs flex items-center justify-center shrink-0"
        :disabled="!inputMessage.trim() || isLoading"
        title="Kirim Pesan"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
      </button>
    </form>

    <!-- Character Limit Counter -->
    <div class="flex justify-between items-center px-1 text-[10px] text-slate-400">
      <span>Tekan Enter untuk mengirim</span>
      <span :class="{'text-rose-500 font-semibold': charCount >= 480}">
        {{ charCount }}/500
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['send']);
const inputMessage = ref('');
const inputRef = ref(null);

const charCount = computed(() => inputMessage.value.length);

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
