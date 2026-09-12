<template>
  <div class="admin-page" :class="themeClass">
    <div class="page-hero" :class="themeClass">
      <div class="hero-icon">🎯</div>
      <h1 class="hero-title">Ekstrakurikuler</h1>
      <p class="hero-subtitle">Daftar ekstrakurikuler yang dapat diikuti siswa.</p>
    </div>

    <div class="admin-layout">
      <main class="admin-main">
        <h2 class="section-title">Daftar Ekstrakurikuler</h2>
        <div class="ekskul-grid">
          <article v-for="(eks, i) in daftarEkskul" :key="eks.id" class="ekskul-card" :class="themeClass">
            <div class="ekskul-icon">{{ eks.icon }}</div>
            <h3 class="ekskul-nama">{{ eks.nama }}</h3>
            <p class="ekskul-desc">{{ eks.desc }}</p>
            <button
              type="button"
              class="ekskul-btn"
              :class="[themeClass, { terpilih: pilihanId === eks.id }]"
              @click="pilihEkskul(eks.id)"
            >
              {{ pilihanId === eks.id ? 'Terpilih' : 'Pilih' }}
            </button>
          </article>
        </div>

        <div v-if="pilihanId" class="card-pilihan" :class="themeClass">
          <h2 class="card-title">Pilihan Anda</h2>
          <p class="pilihan-text">{{ pilihanNama }}</p>
          <p class="pilihan-hint">Konfirmasi pendaftaran dapat dilakukan melalui wali kelas atau administrasi.</p>
        </div>
      </main>

      <aside class="admin-sidebar">
        <div class="sidebar-card" :class="themeClass">
          <h3 class="sidebar-title">Informasi</h3>
          <ul class="info-list">
            <li>Siswa dapat memilih satu atau lebih ekstrakurikuler.</li>
            <li>Jadwal dan tempat akan diinformasikan oleh sekolah.</li>
            <li>Pilihan dapat diubah pada periode yang ditentukan.</li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const pilihanId = ref(null);

const daftarEkskul = ref([
  { id: 'pramuka', nama: 'Pramuka', icon: '🏕️', desc: 'Kegiatan kepramukaan dan keterampilan hidup.' },
  { id: 'pmr', nama: 'PMR', icon: '🩹', desc: 'Palang Merah Remaja, dasar-dasar pertolongan pertama.' },
  { id: 'paskibra', nama: 'Paskibra', icon: '🇮🇩', desc: 'Pasukan pengibar bendera.' },
  { id: 'paduan_suara', nama: 'Paduan Suara', icon: '🎤', desc: 'Paduan suara dan vokal grup.' },
  { id: 'olahraga', nama: 'Olahraga', icon: '⚽', desc: 'Futsal, basket, atau cabang olahraga lainnya.' },
  { id: 'teater', nama: 'Teater', icon: '🎭', desc: 'Seni peran dan pementasan.' },
  { id: 'it_club', nama: 'IT Club', icon: '💻', desc: 'Programming dan teknologi informasi.' },
  { id: 'rohani', nama: 'Rohani', icon: '✝️', desc: 'Kegiatan kerohanian dan ibadah.' },
]);

const pilihanNama = computed(() => {
  const e = daftarEkskul.value.find((x) => x.id === pilihanId.value);
  return e ? e.nama : '';
});

function pilihEkskul(id) {
  pilihanId.value = pilihanId.value === id ? null : id;
}
</script>

<style scoped>
.admin-page { padding-bottom: 2rem; }
.page-hero {
  padding: 1.75rem 2rem;
  border-radius: 20px;
  margin-bottom: 1.5rem;
  text-align: center;
}
.page-hero.theme-laki-laki {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%);
  box-shadow: 0 10px 40px rgba(15, 118, 110, 0.25);
}
.page-hero.theme-perempuan {
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #8b5cf6 100%);
  box-shadow: 0 10px 40px rgba(91, 33, 182, 0.25);
}
.hero-icon { font-size: 2.25rem; margin-bottom: 0.35rem; }
.hero-title { font-size: 1.45rem; font-weight: 800; color: #fff; margin: 0 0 0.25rem 0; }
.hero-subtitle { font-size: 0.95rem; color: rgba(255,255,255,0.92); margin: 0; }

.admin-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start; }
@media (max-width: 900px) { .admin-layout { grid-template-columns: 1fr; } }
.admin-main { display: flex; flex-direction: column; gap: 1.5rem; }

.section-title { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem 0; }

.ekskul-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
}

.ekskul-card {
  padding: 1.35rem 1.5rem;
  background: #fff;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 12px rgba(0,0,0,0.04);
  text-align: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.ekskul-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
.ekskul-card.theme-laki-laki { border-top: 4px solid #0f766e; }
.ekskul-card.theme-perempuan { border-top: 4px solid #7c3aed; }

.ekskul-icon { font-size: 2.25rem; margin-bottom: 0.5rem; }
.ekskul-nama { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem 0; }
.ekskul-desc { font-size: 0.85rem; color: #64748b; margin: 0 0 1rem 0; line-height: 1.4; }

.ekskul-btn {
  width: 100%;
  padding: 0.6rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.ekskul-btn.theme-laki-laki:hover { border-color: #0f766e; color: #0f766e; }
.ekskul-btn.theme-perempuan:hover { border-color: #7c3aed; color: #5b21b6; }
.ekskul-btn.theme-laki-laki.terpilih { background: #0f766e; border-color: #0f766e; color: #fff; }
.ekskul-btn.theme-perempuan.terpilih { background: #7c3aed; border-color: #7c3aed; color: #fff; }

.card-pilihan {
  padding: 1.25rem 1.5rem;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
}
.card-pilihan.theme-laki-laki { background: #f0fdfa; border-color: #99f6e4; }
.card-pilihan.theme-perempuan { background: #f5f3ff; border-color: #ddd6fe; }
.card-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem 0; }
.pilihan-text { font-size: 1.1rem; font-weight: 600; margin: 0 0 0.35rem 0; }
.pilihan-hint { font-size: 0.85rem; color: #64748b; margin: 0; }

.sidebar-card { padding: 1.25rem 1.5rem; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
.sidebar-card.theme-laki-laki { border-top: 3px solid #0f766e; }
.sidebar-card.theme-perempuan { border-top: 3px solid #7c3aed; }
.sidebar-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem 0; }
.info-list { margin: 0; padding-left: 1.2rem; color: #475569; font-size: 0.9rem; line-height: 1.7; }
.info-list li { margin-bottom: 0.5rem; }
</style>
