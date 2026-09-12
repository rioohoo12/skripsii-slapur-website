<template>
  <div class="page-grid">
    <section class="panel">
      <div class="head">
        <div>
          <h2>Pengumuman</h2>
          <p>Sampaikan perubahan jadwal, tugas tambahan, dan informasi ujian.</p>
        </div>
        <button class="primary-btn" type="button">Buat Pengumuman</button>
      </div>
      <ul class="announce-list">
        <li v-for="item in announcements" :key="item.title">
          <p class="title">{{ item.title }}</p>
          <p class="meta">{{ item.className }} - {{ item.time }}</p>
        </li>
      </ul>
    </section>

    <section class="panel">
      <h3>Pesan / Komunikasi Kelas</h3>
      <p class="sub">Ruang diskusi cepat antara guru dan siswa.</p>
      <ul class="chat-list">
        <li v-for="msg in messages" :key="msg.sender + msg.time">
          <p class="sender">{{ msg.sender }}</p>
          <p class="text">{{ msg.text }}</p>
          <p class="time">{{ msg.time }}</p>
        </li>
      </ul>
    </section>
  </div>
</template>

<script setup>
const announcements = [
  { title: 'Jadwal remedial Matematika dipindah ke Jumat', className: 'X IPA 1', time: 'Hari ini, 10:45' },
  { title: 'Pengumpulan proyek diperpanjang 2 hari', className: 'XI IPA 2', time: 'Kemarin, 14:00' },
  { title: 'Ujian formatif bab 3 pekan depan', className: 'X IPA 2', time: '20 Feb 2026, 09:30' },
];

const messages = [
  { sender: 'Ketua Kelas X IPA 1', text: 'Bu, apakah file kisi-kisi sudah bisa diunduh?', time: '09:12' },
  { sender: 'Guru', text: 'Sudah saya upload di menu Materi, silakan cek.', time: '09:18' },
  { sender: 'Bunga Maharani', text: 'Terima kasih Bu, sudah berhasil.', time: '09:19' },
];
</script>

<style scoped>
.page-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
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
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.8rem;
}
h2,
h3 {
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
.announce-list,
.chat-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 0.65rem;
}
.announce-list li,
.chat-list li {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.65rem;
}
.title {
  margin: 0;
  color: #0f172a;
  font-weight: 700;
}
.meta,
.sub {
  margin: 0.25rem 0 0;
  font-size: 0.82rem;
}
.sender {
  margin: 0;
  color: #1e293b;
  font-size: 0.84rem;
  font-weight: 700;
}
.text {
  margin: 0.2rem 0 0;
  color: #334155;
}
.time {
  margin: 0.2rem 0 0;
  font-size: 0.75rem;
  color: #94a3b8;
}
@media (max-width: 980px) {
  .page-grid {
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
