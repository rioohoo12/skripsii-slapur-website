<template>
  <div class="page-card" :class="themeClass">
    <div v-if="$slots.header" class="page-card-header">
      <slot name="header" />
    </div>
    <div class="page-card-body">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  jenisKelamin: { type: String, default: 'laki-laki' },
});

const themeClass = computed(() => `theme-${props.jenisKelamin}`);
</script>

<style scoped>
.page-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.page-card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-weight: 600;
  color: #1e293b;
}
.page-card.theme-laki-laki .page-card-header {
  border-bottom-color: #99f6e4;
  color: #134e4a;
  font-weight: 700;
}
.page-card.theme-perempuan .page-card-header {
  border-bottom-color: #ddd6fe;
  color: #4c1d95;
  font-weight: 700;
}
.page-card-body {
  padding: 1.5rem;
  color: #1e293b;
}
</style>
