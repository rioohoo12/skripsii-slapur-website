<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Materi Pembelajaran</h2>
          <p>Lihat dan unduh materi yang telah dibagikan oleh guru-guru Anda.</p>
        </div>
      </div>
      <div v-if="loading" style="text-align:center; padding: 2rem;">Memuat materi...</div>
      <div v-else-if="materials.length === 0" style="text-align:center; padding: 2rem; color: #64748b;">Belum ada materi untuk kelas Anda.</div>
      <div v-else class="material-grid">
        <article v-for="item in materials" :key="item.id" class="material-card">
          <p class="title">{{ item.title }}</p>
          <p class="meta">{{ item.guru?.name }} - {{ item.subject_name }}</p>
          <div class="foot">
            <span class="type-badge">{{ item.type }}</span>
            <button class="ghost-btn" type="button" @click="download(item)">Unduh / Lihat</button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { authApi } from '@/api/auth';

const materials = ref([]);
const loading = ref(true);

const loadMaterials = async () => {
  try {
    const res = await authApi.fetch('/siswa/materials');
    if (res.materials) {
      materials.value = res.materials;
    }
  } catch (error) {
    console.error('Failed to load materials', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadMaterials();
});

function download(material) {
  if (material.file_url) {
    window.open(material.file_url, '_blank');
  } else {
    alert('Tidak ada file tautan untuk: ' + material.title);
  }
}
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
  padding: 1.5rem;
}
.head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}
h2 {
  margin: 0;
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 600;
}
p {
  margin: 0.3rem 0 0;
  color: #64748b;
  font-size: 0.95rem;
}
.material-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}
.material-card {
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  transition: all 0.2s ease;
}
.material-card:hover {
  border-color: #cbd5e1;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.title {
  margin: 0;
  color: #0f172a;
  font-weight: 600;
  font-size: 1.1rem;
  line-height: 1.4;
}
.meta {
  margin: 0.5rem 0 0;
  font-size: 0.85rem;
  color: #64748b;
}
.foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}
.type-badge {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}
.ghost-btn {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #3b82f6;
  font-weight: 500;
  border-radius: 6px;
  padding: 0.4rem 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}
.ghost-btn:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
}
@media (max-width: 640px) {
  .head {
    flex-direction: column;
  }
}
</style>
