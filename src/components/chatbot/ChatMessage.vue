<template>
  <div 
    :class="[
      'flex flex-col max-w-[85%] sm:max-w-[80%] rounded-2xl p-3.5 text-sm leading-relaxed transition-all shadow-xs',
      isUser 
        ? 'bg-teal-700 text-white self-end rounded-tr-none shadow-teal-900/10' 
        : isError
          ? 'bg-rose-50 border border-rose-200 text-rose-800 self-start rounded-tl-none'
          : 'bg-white border border-slate-200/80 text-slate-800 self-start rounded-tl-none shadow-slate-100'
    ]"
  >
    <!-- Source & Intent Badge (Assistant Only) -->
    <div v-if="!isUser && sourceTag" class="flex items-center gap-1.5 mb-1.5">
      <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200">
        {{ sourceTag }}
      </span>
      <span v-if="message.intent" class="text-[10px] font-medium text-slate-400">
        #{{ message.intent }}
      </span>
    </div>

    <!-- Message Content -->
    <div class="whitespace-pre-line font-sans break-words">
      {{ message.message || message.pesan || message.respons }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
});

const isUser = computed(() => props.message.role === 'user');
const isError = computed(() => props.message.isError);

const sourceTag = computed(() => {
  const src = props.message.source;
  if (src === 'knowledge_base') return 'FAQ Resmi';
  if (src === 'local_db') return 'Data Terverifikasi';
  if (src === 'action') return 'Aksi Sistem';
  if (src === 'llm') return 'Virtual Assistant';
  return null;
});
</script>
