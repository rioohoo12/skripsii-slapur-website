<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Materi Pembelajaran</h2>
          <p>Upload materi PDF/PPT, bagikan ke kelas, dan atur versi terbaru.</p>
        </div>
        <button class="primary-btn" type="button">Upload Materi</button>
      </div>
      <div class="material-grid">
        <article v-for="item in materials" :key="item.title" class="material-card">
          <p class="title">{{ item.title }}</p>
          <p class="meta">{{ item.className }} - {{ item.type }}</p>
          <div class="foot">
            <span>Diperbarui {{ item.updatedAt }}</span>
            <button class="ghost-btn" type="button">Unduh</button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
const materials = [
  { title: 'Bab 1: Sistem Persamaan Linear', className: 'X IPA 1', type: 'PDF', updatedAt: '2 hari lalu' },
  { title: 'Slide Trigonometri Dasar', className: 'X IPA 2', type: 'PPT', updatedAt: '5 hari lalu' },
  { title: 'Modul Limit Fungsi', className: 'XI IPA 1', type: 'PDF', updatedAt: '1 minggu lalu' },
  { title: 'Video Penjelasan Turunan', className: 'XI IPA 2', type: 'Link', updatedAt: '1 minggu lalu' },
];
</script>

<style scoped>
.page-grid {
  display: grid;
  gap: 1rem;
}
.panel {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem;
}
.head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.9rem;
}
h2 {
  margin: 0;
  color: #1e293b;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
}
.primary-btn {
  border: none;
  background: #2563eb;
  color: #fff;
  border-radius: 10px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
}
.material-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}
.material-card {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 12px;
  padding: 0.8rem;
}
.title {
  margin: 0;
  color: #0f172a;
  font-weight: 700;
}
.meta {
  margin: 0.25rem 0 0;
  font-size: 0.83rem;
}
.foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.6rem;
  font-size: 0.78rem;
  color: #475569;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
  border-radius: 8px;
  padding: 0.3rem 0.6rem;
  cursor: pointer;
}
@media (max-width: 800px) {
  .material-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 640px) {
  .head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
