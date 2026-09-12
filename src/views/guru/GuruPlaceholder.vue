<template>
  <div class="placeholder-page">
    <div class="placeholder-card">
      <h2>{{ title }}</h2>
      <p>Fitur ini akan segera dilengkapi. Gunakan menu untuk navigasi.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Halaman' },
});
</script>

<style scoped>
.placeholder-page {
  max-width: 480px;
}
.placeholder-card {
  padding: 2rem;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}
.placeholder-card h2 {
  font-size: 1.2rem;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}
.placeholder-card p {
  color: #64748b;
  margin: 0;
  font-size: 0.95rem;
}
</style>
