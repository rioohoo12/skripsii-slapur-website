<template>
  <div class="app" :class="themeClass">
    <router-view />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const themeClass = computed(() => {
  const jk = route.params.jenisKelamin || route.query.jk || 'laki-laki';
  return `theme-${jk}`;
});
</script>

<style>
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
}
body {
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  background: #f1f5f9;
  color: #1e293b;
  min-height: 100vh;
}
.app.theme-laki-laki {
  --primary: #0f766e;
  --primary-light: #0d9488;
  --primary-dark: #134e4a;
  --primary-bg: #ccfbf1;
  --primary-hover: #99f6e4;
}
.app.theme-perempuan {
  --primary: #7c3aed;
  --primary-light: #8b5cf6;
  --primary-dark: #4c1d95;
  --primary-bg: #ede9fe;
  --primary-hover: #ddd6fe;
}
#app {
  min-height: 100vh;
}
</style>
