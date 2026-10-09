<template>
  <div 
    :class="[
      'message-bubble',
      isUser ? 'user-msg' : 'bot-msg'
    ]"
  >
    <!-- Source & Intent Badge (Assistant Only if available) -->
    <div v-if="!isUser && sourceTag" class="source-badge">
      <span class="badge-dot">●</span> {{ sourceTag }}
    </div>

    <!-- Message Content -->
    <div class="msg-content" v-html="formattedMessage"></div>

    <!-- Time -->
    <div class="msg-time">
      {{ timeFormat }}
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

const timeFormat = computed(() => {
  const now = new Date();
  return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const formattedMessage = computed(() => {
  const raw = props.message.message || props.message.pesan || props.message.respons || '';
  if (!raw) return '';
  
  let formatted = raw
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br/>');

  return formatted;
});

const sourceTag = computed(() => {
  const src = props.message.source;
  if (src === 'knowledge_base') return 'FAQ Resmi SLAPUR';
  if (src === 'local_db') return 'Data Terverifikasi';
  if (src === 'action') return 'Aksi Sistem';
  return null;
});
</script>

<style scoped>
.message-bubble {
  max-width: 85%;
  padding: 0.9rem 1.15rem;
  border-radius: 16px;
  font-size: 0.9rem;
  line-height: 1.6;
  word-break: break-word;
  font-family: inherit;
}

.bot-msg {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #334155;
  align-self: flex-start;
  border-top-left-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.user-msg {
  background: #0d6e59;
  color: #ffffff;
  align-self: flex-end;
  border-top-right-radius: 4px;
  box-shadow: 0 2px 8px rgba(13, 110, 89, 0.15);
}

.source-badge {
  font-size: 0.7rem;
  font-weight: 700;
  color: #0d6e59;
  margin-bottom: 0.35rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.badge-dot {
  color: #10b981;
}

.msg-content :deep(strong) {
  font-weight: 700;
}

.user-msg .msg-content :deep(strong) {
  color: #ffffff;
}

.msg-time {
  font-size: 0.7rem;
  opacity: 0.7;
  margin-top: 0.4rem;
  text-align: right;
}
</style>
