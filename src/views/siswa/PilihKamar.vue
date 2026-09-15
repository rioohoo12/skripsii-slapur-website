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

    <!-- Status Kamar Murid -->
    <div v-if="studentRoomStatus" class="status-alert success" :class="themeClass">
      <div class="status-icon">✅</div>
      <div class="status-text">
        <strong>Anda telah memilih kamar:</strong><br/>
        Kamar No. {{ studentRoomStatus.nomor_kamar }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat data kamar...</p>
    </div>

    <!-- Daftar asrama -->
    <div v-else-if="!studentRoomStatus" class="asrama-list">
      <article
        v-for="(asrama, idx) in asramaGroups"
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
            <span class="asrama-count">{{ asrama.kamarList.filter(k => k.tersedia).length }} kamar tersedia</span>
          </div>
        </div>
        <div class="kamar-grid">
          <button
            v-for="kamar in asrama.kamarList"
            :key="kamar.id"
            type="button"
            class="kamar-btn"
            :class="[themeClass, { 
                selected: selectedKamar && selectedKamar.id === kamar.id,
                full: !kamar.tersedia
            }]"
            :disabled="!kamar.tersedia"
            @click="toggleKamar(kamar)"
          >
            <div class="kamar-content">
                <span class="kamar-num">{{ kamar.nomor_kamar }}</span>
                <span class="kamar-occupancy" v-if="kamar.tersedia">{{ kamar.current_occupancy }}/{{ kamar.kapasitas }}</span>
                <span class="kamar-occupancy full-text" v-else>Penuh</span>
            </div>
          </button>
        </div>
      </article>
    </div>

    <!-- Bar pilihan (sticky bottom) -->
    <Transition name="slide-up">
      <div v-if="selectedKamar && !studentRoomStatus" class="selected-bar" :class="themeClass">
        <span class="selected-label">Kamar dipilih</span>
        <span class="selected-value">Kamar {{ selectedKamar.nomor_kamar }}</span>
        <button type="button" class="selected-action-btn" @click="showConfirmModal = true">
          Pilih Sekarang
        </button>
        <button type="button" class="selected-clear" @click="selectedKamar = null" aria-label="Batal pilih">
          ✕
        </button>
      </div>
    </Transition>

    <!-- Modal Konfirmasi -->
    <div v-if="showConfirmModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Konfirmasi Pilihan Kamar</h3>
            <p>Anda akan memilih <strong>Kamar {{ selectedKamar?.nomor_kamar }}</strong>.</p>
            <p class="warning-text">Pilihan ini tidak dapat diubah setelah dikonfirmasi.</p>
            
            <div class="modal-actions">
                <button class="btn-cancel" @click="showConfirmModal = false" :disabled="isSubmitting">Batal</button>
                <button class="btn-confirm" @click="confirmPilihKamar" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Menyimpan...' : 'Ya, Pilih Kamar' }}
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { pendaftaranApi } from '@/api/pendaftaran.js';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const daftarKamar = ref([]);
const selectedKamar = ref(null);
const studentRoomStatus = ref(null);
const isLoading = ref(true);

const showConfirmModal = ref(false);
const isSubmitting = ref(false);

const fetchRooms = async () => {
    isLoading.value = true;
    try {
        const [statusRes, kamarRes] = await Promise.all([
            pendaftaranApi.getStatus(),
            pendaftaranApi.getKamar()
        ]);
        
        if (statusRes.room && statusRes.room.kamar_id) {
            studentRoomStatus.value = statusRes.room;
        }

        if (kamarRes.kamar) {
            daftarKamar.value = kamarRes.kamar;
        }
    } catch (e) {
        alert(e.message || 'Gagal mengambil data kamar');
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    fetchRooms();
});

const asramaGroups = computed(() => {
   const groups = {};
   daftarKamar.value.forEach(kamar => {
       // Check if nomor_kamar exists and has at least 1 character
       if (!kamar.nomor_kamar || kamar.nomor_kamar.length === 0) return;

       const prefix = kamar.nomor_kamar.charAt(0).toUpperCase();
       if (!groups[prefix]) {
           let nama = 'Asrama ' + prefix;
           let icon = '🏢';
           if (props.jenisKelamin === 'perempuan') {
               if (prefix === 'A') { nama = 'Asrama Krisan'; icon = '🌸'; }
               else if (prefix === 'B') { nama = 'Asrama Melati'; icon = '🌼'; }
               else if (prefix === 'C') { nama = 'Asrama Mawar'; icon = '🌹'; }
           } else {
               if (prefix === 'A') { nama = 'Asrama Anex'; icon = '🏢'; }
               else if (prefix === 'B') { nama = 'Asrama Cendrawasih'; icon = '🐦'; }
               else if (prefix === 'C') { nama = 'Asrama Hawk'; icon = '🦅'; }
           }
           groups[prefix] = {
               id: prefix,
               nama,
               icon,
               kamarList: []
           };
       }
       groups[prefix].kamarList.push(kamar);
   });
   return Object.values(groups);
});

function toggleKamar(kamar) {
  if (!kamar.tersedia) return;
  if (selectedKamar.value && selectedKamar.value.id === kamar.id) {
      selectedKamar.value = null;
  } else {
      selectedKamar.value = kamar;
  }
}

async function confirmPilihKamar() {
    if (!selectedKamar.value) return;
    
    isSubmitting.value = true;
    try {
        const res = await pendaftaranApi.pilihKamar({ kamar_id: selectedKamar.value.id });
        alert(res.message || 'Kamar berhasil dipilih');
        showConfirmModal.value = false;
        studentRoomStatus.value = res.room;
        // Refresh kamar data to update availability
        const kamarRes = await pendaftaranApi.getKamar();
        daftarKamar.value = kamarRes.kamar;
    } catch (e) {
        alert(e.message || 'Gagal memilih kamar');
        showConfirmModal.value = false;
    } finally {
        isSubmitting.value = false;
    }
}
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

/* Status Alert */
.status-alert {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.status-alert.theme-laki-laki {
    background: #f0fdfa;
    border-color: #ccfbf1;
}
.status-alert.theme-perempuan {
    background: #f5f3ff;
    border-color: #ede9fe;
}
.status-icon {
    font-size: 2rem;
}
.status-text {
    font-size: 1.05rem;
    color: #1e293b;
    line-height: 1.4;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 3rem 0;
    color: #64748b;
}
.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top-color: #94a3b8;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

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
  grid-template-columns: repeat(auto-fill, minmax(75px, 1fr));
  gap: 0.75rem;
}

.kamar-btn {
  aspect-ratio: 1;
  min-width: 0;
  padding: 0;
  border: 2px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}

.kamar-btn:hover:not(:disabled) {
  border-color: #cbd5e1;
  background: #f1f5f9;
  color: #1e293b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.kamar-btn:disabled.full {
  background: #f1f5f9;
  border-color: #e2e8f0;
  opacity: 0.6;
  cursor: not-allowed;
}

.kamar-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.kamar-num {
    font-size: 1.1rem;
    font-weight: 700;
}

.kamar-occupancy {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
}

.kamar-occupancy.full-text {
    color: #ef4444;
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
  font-size: 1.15rem;
  font-weight: 700;
}

.selected-action-btn {
    background: #fff;
    color: #1e293b;
    border: none;
    padding: 0.5rem 1.25rem;
    border-radius: 999px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.selected-action-btn:hover {
    transform: scale(1.05);
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

/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    padding: 1.5rem;
}
.modal-content {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    text-align: center;
}
.modal-content h3 {
    margin: 0 0 1rem;
    font-size: 1.4rem;
    color: #0f172a;
}
.modal-content p {
    color: #475569;
    margin-bottom: 0.5rem;
}
.warning-text {
    color: #ef4444 !important;
    font-size: 0.85rem;
    margin-bottom: 1.5rem !important;
}
.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}
.modal-actions button {
    flex: 1;
    padding: 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.btn-cancel {
    background: #f1f5f9;
    color: #475569;
}
.btn-cancel:hover:not(:disabled) {
    background: #e2e8f0;
}
.btn-confirm {
    background: #0f766e;
    color: #fff;
}
.btn-confirm:hover:not(:disabled) {
    background: #0d9488;
}
.theme-perempuan .btn-confirm {
    background: #6d28d9;
}
.theme-perempuan .btn-confirm:hover:not(:disabled) {
    background: #5b21b6;
}
.modal-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
