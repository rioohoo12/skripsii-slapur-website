<template>
  <PageCard :jenis-kelamin="jenisKelamin">
    <template #header>Data Kelas</template>
    <p class="placeholder">Informasi data kelas Anda. Gunakan submenu <strong>Review Kelas</strong> untuk detail.</p>
    <router-link :to="`/siswa/${jenisKelamin}/data-kelas/review`" class="btn-link">Lihat Review Kelas →</router-link>
  </PageCard>
</template>

<script setup>
import PageCard from '@/components/PageCard.vue';
defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
</script>

<style scoped>
.placeholder {
  color: #94a3b8;
  margin-bottom: 1rem;
}
.btn-link {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 10px;
  background: var(--primary-bg);
  color: var(--primary-dark);
  font-weight: 500;
  text-decoration: none;
}
.btn-link:hover {
  opacity: 0.9;
}
</style>
