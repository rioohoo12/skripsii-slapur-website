<template>
  <div class="pilih-kamar-page" :class="themeClass">
    <!-- Header -->
    <div class="page-hero" :class="themeClass">
      <div class="hero-icon">🏠</div>
      <h1 class="hero-title">Pilih Kamar</h1>
      <p class="hero-subtitle">Pilih asrama dan nomor kamar yang tersedia untuk pendaftaran.</p>
      <span class="hero-badge" :class="themeClass">
        {{ jenisKelamin === 'perempuan' ? 'Asrama Putri' : 'Asrama Putra' }}
      </span>
    </div>

    <!-- Daftar asrama -->
    <div class="asrama-list">
      <article
        v-for="(asrama, idx) in daftarAsrama"
        :key="asrama.id"
        class="asrama-card"
        :class="themeClass"
      >
        <div class="asrama-card-accent" :class="'accent-' + (idx % 3)"></div>
        <div class="asrama-card-head">
          <div class="asrama-icon-wrap" :class="themeClass">
            <span class="asrama-icon">{{ asrama.icon }}</span>
          </div>
          <div class="asrama-meta">
            <h2 class="asrama-nama">{{ asrama.nama }}</h2>
            <span class="asrama-count">{{ asrama.nomorKamar.length }} kamar tersedia</span>
          </div>
        </div>
        <div class="kamar-grid">
          <button
            v-for="no in asrama.nomorKamar"
            :key="no"
            type="button"
            class="kamar-btn"
            :class="[themeClass, { selected: selectedKamar === asrama.id + '-' + no }]"
            @click="toggleKamar(asrama.id, no)"
          >
            <span class="kamar-num">{{ no }}</span>
          </button>
        </div>
      </article>
    </div>

    <!-- Bar pilihan (sticky bottom) -->
    <Transition name="slide-up">
      <div v-if="selectedKamarDisplay" class="selected-bar" :class="themeClass">
        <span class="selected-label">Kamar dipilih</span>
        <span class="selected-value">{{ selectedKamarDisplay }}</span>
        <button type="button" class="selected-clear" @click="selectedKamar = ''" aria-label="Batal pilih">
          ✕
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const selectedKamar = ref('');

const selectedKamarDisplay = computed(() => {
  if (!selectedKamar.value) return '';
  const [id, no] = selectedKamar.value.split('-');
  const list = props.jenisKelamin === 'perempuan' ? asramaPerempuan : asramaLakiLaki;
  const asrama = list.find((a) => a.id === id);
  const nama = asrama ? asrama.nama : id;
  return `${nama}, No. ${no}`;
});

function toggleKamar(asramaId, no) {
  const key = asramaId + '-' + no;
  selectedKamar.value = selectedKamar.value === key ? '' : key;
}

function range(start, end) {
  const arr = [];
  for (let i = start; i <= end; i++) arr.push(i);
  return arr;
}

const asramaLakiLaki = [
  { id: 'anex', nama: 'Asrama Anex', icon: '🏢', nomorKamar: range(101, 148) },
  { id: 'cendrawasih', nama: 'Asrama Cendrawasih', icon: '🐦', nomorKamar: range(201, 252) },
  { id: 'hawk', nama: 'Asrama Hawk', icon: '🦅', nomorKamar: range(301, 355) },
];

const asramaPerempuan = [
  { id: 'krisan', nama: 'Asrama Krisan', icon: '🌸', nomorKamar: range(101, 148) },
  { id: 'melati', nama: 'Asrama Melati', icon: '🌼', nomorKamar: range(201, 252) },
  { id: 'mawar', nama: 'Asrama Mawar', icon: '🌹', nomorKamar: range(301, 355) },
];

const daftarAsrama = computed(() =>
  props.jenisKelamin === 'perempuan' ? asramaPerempuan : asramaLakiLaki
);
</script>

<style scoped>
.pilih-kamar-page {
  padding-bottom: 5rem;
  min-height: 100vh;
}

/* ----- Hero ----- */
.page-hero {
  padding: 2rem 1.75rem;
  border-radius: 20px;
  margin-bottom: 2rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  opacity: 0.06;
  background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h40v40H0z' fill='none'/%3E%3Cpath d='M20 8v24M8 20h24' stroke='%23000' stroke-width='.5' fill='none'/%3E%3C/svg%3E");
}

.page-hero.theme-laki-laki {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 50%, #2dd4bf 100%);
  box-shadow: 0 10px 40px rgba(15, 118, 110, 0.25);
}

.page-hero.theme-perempuan {
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #8b5cf6 100%);
  box-shadow: 0 10px 40px rgba(91, 33, 182, 0.25);
}

.hero-icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
}

.hero-title {
  font-size: 1.6rem;
  font-weight: 800;
  color: #fff;
  margin: 0 0 0.35rem 0;
  letter-spacing: -0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 1rem 0;
  max-width: 420px;
  margin-left: auto;
  margin-right: auto;
}

.hero-badge {
  display: inline-block;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: #fff;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(6px);
}

/* ----- Asrama cards ----- */
.asrama-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.asrama-card {
  position: relative;
  background: #fff;
  border-radius: 20px;
  padding: 1.75rem 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.asrama-card-accent {
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  border-radius: 20px 0 0 20px;
}

.asrama-card.theme-laki-laki .accent-0 { background: linear-gradient(180deg, #0f766e, #2dd4bf); }
.asrama-card.theme-laki-laki .accent-1 { background: linear-gradient(180deg, #0d9488, #5eead4); }
.asrama-card.theme-laki-laki .accent-2 { background: linear-gradient(180deg, #14b8a6, #99f6e4); }

.asrama-card.theme-perempuan .accent-0 { background: linear-gradient(180deg, #5b21b6, #8b5cf6); }
.asrama-card.theme-perempuan .accent-1 { background: linear-gradient(180deg, #7c3aed, #a78bfa); }
.asrama-card.theme-perempuan .accent-2 { background: linear-gradient(180deg, #6d28d9, #c4b5fd); }

.asrama-card-head {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.asrama-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  flex-shrink: 0;
}

.asrama-icon-wrap.theme-laki-laki {
  background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
  box-shadow: 0 4px 14px rgba(15, 118, 110, 0.2);
}

.asrama-icon-wrap.theme-perempuan {
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  box-shadow: 0 4px 14px rgba(124, 58, 237, 0.2);
}

.asrama-meta {
  flex: 1;
  min-width: 0;
}

.asrama-nama {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.2rem 0;
}

.asrama-count {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

/* ----- Room grid ----- */
.kamar-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
  gap: 0.5rem;
}

.kamar-btn {
  aspect-ratio: 1;
  min-width: 0;
  padding: 0;
  border: 2px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
  font-size: 0.9rem;
  font-weight: 700;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
}

.kamar-btn:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
  color: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.kamar-btn.theme-laki-laki.selected {
  border-color: #0f766e;
  background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
  color: #0f766e;
  box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.25);
}

.kamar-btn.theme-perempuan.selected {
  border-color: #7c3aed;
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  color: #5b21b6;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.25);
}

/* ----- Selected bar (sticky bottom) ----- */
.selected-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  padding-left: max(1.5rem, env(safe-area-inset-left));
  padding-right: max(1.5rem, env(safe-area-inset-right));
  padding-bottom: max(1rem, env(safe-area-inset-bottom));
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.1);
  z-index: 50;
}

.selected-bar.theme-laki-laki {
  background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
  color: #fff;
}

.selected-bar.theme-perempuan {
  background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
  color: #fff;
}

.selected-label {
  font-size: 0.85rem;
  font-weight: 600;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.selected-value {
  flex: 1;
  font-size: 1.05rem;
  font-weight: 700;
}

.selected-clear {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: none;
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background 0.2s;
}

.selected-clear:hover {
  background: rgba(255, 255, 255, 0.4);
}

/* Transition */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease, opacity 0.25s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
