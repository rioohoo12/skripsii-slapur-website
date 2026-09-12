<template>
  <div class="admin-page" :class="themeClass">
    <div class="page-hero" :class="themeClass">
      <div class="hero-icon">📄</div>
      <h1 class="hero-title">Permohonan Surat</h1>
      <p class="hero-subtitle">Ajukan permohonan surat keterangan, surat izin, dan jenis surat lainnya.</p>
    </div>

    <div class="admin-layout">
      <main class="admin-main">
        <div class="card-form" :class="themeClass">
          <h2 class="card-title">Ajukan Permohonan</h2>
          <form class="form-surat" @submit.prevent="submitPermohonan">
            <div class="form-group">
              <label for="jenis">Jenis Surat</label>
              <select id="jenis" v-model="form.jenisSurat" required>
                <option value="">— Pilih jenis surat —</option>
                <option value="keterangan">Surat Keterangan</option>
                <option value="izin">Surat Izin</option>
                <option value="aktif">Surat Keterangan Aktif</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label for="keterangan">Keterangan / Keperluan</label>
              <textarea
                id="keterangan"
                v-model="form.keterangan"
                rows="4"
                placeholder="Jelaskan keperluan surat..."
              ></textarea>
            </div>
            <button type="submit" class="btn-submit" :class="themeClass">Kirim Permohonan</button>
          </form>
        </div>

        <div class="card-list" :class="themeClass">
          <h2 class="card-title">Riwayat Permohonan</h2>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Jenis Surat</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in riwayatPermohonan" :key="i">
                  <td>{{ i + 1 }}</td>
                  <td>{{ item.jenis }}</td>
                  <td>{{ item.tanggal }}</td>
                  <td><span class="badge-status" :class="item.statusClass">{{ item.status }}</span></td>
                </tr>
                <tr v-if="!riwayatPermohonan.length" class="empty-row">
                  <td colspan="4">Belum ada riwayat permohonan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>

      <aside class="admin-sidebar">
        <div class="sidebar-card" :class="themeClass">
          <h3 class="sidebar-title">Informasi</h3>
          <ul class="info-list">
            <li>Pilih jenis surat dan isi keterangan keperluan.</li>
            <li>Permohonan akan diproses oleh pihak administrasi.</li>
            <li>Status dapat dilihat pada riwayat permohonan.</li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

const props = defineProps({ jenisKelamin: { type: String, default: 'laki-laki' } });
const themeClass = computed(() => 'theme-' + props.jenisKelamin);

const form = reactive({
  jenisSurat: '',
  keterangan: '',
});

const riwayatPermohonan = ref([]);

function submitPermohonan() {
  if (!form.jenisSurat) return;
  const labels = { keterangan: 'Surat Keterangan', izin: 'Surat Izin', aktif: 'Surat Keterangan Aktif', lainnya: 'Lainnya' };
  riwayatPermohonan.value.unshift({
    jenis: labels[form.jenisSurat] || form.jenisSurat,
    tanggal: new Date().toLocaleDateString('id-ID'),
    status: 'Menunggu',
    statusClass: 'status-pending',
  });
  form.jenisSurat = '';
  form.keterangan = '';
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
.admin-main { display: flex; flex-direction: column; gap: 1.25rem; }

.card-form, .card-list {
  padding: 1.5rem 2rem;
  background: #fff;
  border-radius: 18px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.card-form.theme-laki-laki, .card-list.theme-laki-laki { border-top: 4px solid #0f766e; }
.card-form.theme-perempuan, .card-list.theme-perempuan { border-top: 4px solid #7c3aed; }
.card-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 1.25rem 0; }

.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
}
.form-group textarea { resize: vertical; min-height: 100px; }
.btn-submit {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  color: #fff;
}
.btn-submit.theme-laki-laki { background: #0f766e; }
.btn-submit.theme-perempuan { background: #7c3aed; }
.btn-submit:hover { opacity: 0.9; }

.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
.data-table th { background: #f8fafc; font-weight: 600; color: #64748b; }
.data-table .empty-row td { text-align: center; color: #94a3b8; padding: 2rem; }
.badge-status { padding: 0.3rem 0.7rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; }
.badge-status.status-pending { background: #fef3c7; color: #92400e; }
.badge-status.status-ok { background: #d1fae5; color: #065f46; }
.badge-status.status-no { background: #fee2e2; color: #991b1b; }

.sidebar-card { padding: 1.25rem 1.5rem; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
.sidebar-card.theme-laki-laki { border-top: 3px solid #0f766e; }
.sidebar-card.theme-perempuan { border-top: 3px solid #7c3aed; }
.sidebar-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem 0; }
.info-list { margin: 0; padding-left: 1.2rem; color: #475569; font-size: 0.9rem; line-height: 1.7; }
.info-list li { margin-bottom: 0.5rem; }
</style>
