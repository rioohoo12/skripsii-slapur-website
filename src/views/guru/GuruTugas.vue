<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Tugas Siswa</h2>
          <p>Buat tugas, atur deadline, dan monitor pengumpulan siswa.</p>
        </div>
        <button class="primary-btn" type="button">Buat Tugas</button>
      </div>
      <ul class="task-list">
        <li v-for="task in tasks" :key="task.title">
          <div>
            <p class="title">{{ task.title }}</p>
            <p class="meta">{{ task.className }} - Deadline {{ task.deadline }}</p>
          </div>
          <div class="right">
            <span class="progress">{{ task.submitted }}/{{ task.total }} dikumpulkan</span>
            <button type="button" class="ghost-btn">Detail</button>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup>
const tasks = [
  { title: 'Latihan SPLDV', className: 'X IPA 1', deadline: '27 Feb 2026 23:59', submitted: 28, total: 32 },
  { title: 'Kuis Aljabar', className: 'XI IPA 2', deadline: '01 Mar 2026 20:00', submitted: 21, total: 29 },
  { title: 'Proyek Fungsi Kuadrat', className: 'X IPA 2', deadline: '05 Mar 2026 21:00', submitted: 6, total: 31 },
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
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.85rem;
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
.task-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.65rem;
}
.task-list li {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.7rem;
}
.title {
  margin: 0;
  color: #0f172a;
  font-weight: 700;
}
.meta {
  margin: 0.25rem 0 0;
  font-size: 0.82rem;
}
.right {
  display: flex;
  align-items: center;
  gap: 0.55rem;
}
.progress {
  font-size: 0.78rem;
  color: #334155;
  background: #e2e8f0;
  border-radius: 999px;
  padding: 0.2rem 0.55rem;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
  border-radius: 8px;
  padding: 0.35rem 0.65rem;
  cursor: pointer;
}
@media (max-width: 680px) {
  .head,
  .task-list li,
  .right {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
