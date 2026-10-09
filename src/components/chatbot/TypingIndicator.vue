<template>
  <div class="typing-indicator">
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="text">Bot sedang menyusun jawaban...</span>
  </div>
</template>

<script setup>
</script>

<style scoped>
.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  color: #64748b;
  padding: 0.5rem 0.75rem;
}

.typing-indicator .dot {
  width: 6px;
  height: 6px;
  background: #0d6e59;
  border-radius: 50%;
  animation: bounce 1.2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}
</style>
