<template>
  <div class="dashboard-home">
    
    <!-- Welcome Banner -->
    <div class="welcome-banner">
      <div class="banner-content">
        <h2 class="welcome-title">Selamat Datang, {{ user?.name || 'Siswa' }} 👋</h2>
        <p class="welcome-subtitle">Semoga hari ini penuh dengan semangat dan hal baik!</p>
        <div class="semester-badge">Semester Ganjil 2025/2026</div>
      </div>
      <div class="banner-illustration">
        <!-- SVG Placeholder for building -->
        <svg viewBox="0 0 400 150" fill="none" xmlns="http://www.w3.org/2000/svg" class="building-svg">
          <path d="M50 150 L50 80 L150 80 L150 150 Z" fill="#93c5fd" />
          <path d="M150 150 L150 50 L250 50 L250 150 Z" fill="#60a5fa" />
          <path d="M250 150 L250 90 L350 90 L350 150 Z" fill="#93c5fd" />
          <path d="M150 50 L200 20 L250 50 Z" fill="#3b82f6" />
          <!-- Windows -->
          <rect x="70" y="100" width="20" height="20" fill="#eff6ff" />
          <rect x="110" y="100" width="20" height="20" fill="#eff6ff" />
          <rect x="170" y="70" width="20" height="20" fill="#eff6ff" />
          <rect x="210" y="70" width="20" height="20" fill="#eff6ff" />
          <rect x="170" y="110" width="20" height="20" fill="#eff6ff" />
          <rect x="210" y="110" width="20" height="20" fill="#eff6ff" />
          <rect x="270" y="110" width="20" height="20" fill="#eff6ff" />
          <rect x="310" y="110" width="20" height="20" fill="#eff6ff" />
          <!-- Sign -->
          <rect x="160" y="55" width="80" height="15" fill="#1e3a8a" rx="2" />
          <text x="200" y="65" fill="white" font-size="8" text-anchor="middle" font-weight="bold">SLAPUR ACADEMIC</text>
        </svg>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon-wrap bg-green-100">
          <span class="stat-icon text-green-600">✅</span>
        </div>
        <div class="stat-info">
          <p class="stat-label">Kehadiran</p>
          <h3 class="stat-value">95%</h3>
          <p class="stat-sub text-green-600 font-medium">Baik</p>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-grid">
      
      <!-- Jadwal Kuliah -->
      <div class="card-base col-span-2">
        <div class="card-header">
          <div class="card-title">
            <span class="header-icon text-blue-600">📅</span>
            <h3>Jadwal Mata Pelajaran</h3>
          </div>
          <span class="card-date text-gray-500 text-sm">Senin, 8 September 2025</span>
        </div>
        <div class="card-body">
          <table class="schedule-table">
            <thead>
              <tr>
                <th>Jam</th>
                <th>Mata Pelajaran</th>
                <th>Guru</th>
                <th>Ruang</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>07:00 - 08:40</td>
                <td>Matematika</td>
                <td>Bu Siti Nurhaliza</td>
                <td>R. 3.1</td>
              </tr>
              <tr>
                <td>08:40 - 10:20</td>
                <td>Bahasa Inggris</td>
                <td>Pak Ahmad Ridho</td>
                <td>R. 3.1</td>
              </tr>
              <tr>
                <td>10:20 - 12:00</td>
                <td>Fisika</td>
                <td>Bu Eka Widhiastuti</td>
                <td>R. 2.2</td>
              </tr>
              <tr>
                <td>13:00 - 14:40</td>
                <td>Kimia</td>
                <td>Bu Siti Nurhaliza</td>
                <td>R. 1.3</td>
              </tr>
              <tr>
                <td>14:40 - 16:20</td>
                <td>Biologi</td>
                <td>Bu Eka Widhiastuti</td>
                <td>R. 2.1</td>
              </tr>
            </tbody>
          </table>
          <a href="#" class="view-all-link">Lihat jadwal lengkap →</a>
        </div>
      </div>

      <!-- Pengumuman Terbaru -->
      <div class="card-base">
        <div class="card-header">
          <div class="card-title">
            <span class="header-icon text-blue-600">📢</span>
            <h3>Pengumuman Terbaru</h3>
          </div>
          <a href="#" class="text-blue-500 text-sm hover:underline">Lihat semua →</a>
        </div>
        <div class="card-body">
          <ul class="announcement-list">
            <li class="announcement-item border-l-blue">
              <div class="announce-header">
                <h4 class="announce-title">Pengisian KRS Semester Ganjil 2025/2026</h4>
                <span class="announce-date">05 Sep 2025</span>
              </div>
              <p class="announce-desc">Silakan lakukan pengisian KRS melalui menu KRS...</p>
            </li>
            <li class="announcement-item border-l-orange">
              <div class="announce-header">
                <h4 class="announce-title">Jadwal Ujian Tengah Semester (UTS)</h4>
                <span class="announce-date">03 Sep 2025</span>
              </div>
              <p class="announce-desc">UTS akan dilaksanakan pada tanggal 15 - 26 Septem...</p>
            </li>
            <li class="announcement-item border-l-green">
              <div class="announce-header">
                <h4 class="announce-title">Pembayaran SPP Bulan September</h4>
                <span class="announce-date">01 Sep 2025</span>
              </div>
              <p class="announce-desc">Pembayaran SPP dapat dilakukan melalui bank yang...</p>
            </li>
            <li class="announcement-item border-l-purple">
              <div class="announce-header">
                <h4 class="announce-title">Program Beasiswa Prestasi</h4>
                <span class="announce-date">28 Agu 2025</span>
              </div>
              <p class="announce-desc">Dibuka pendaftaran beasiswa bagi siswa berprestasi...</p>
            </li>
          </ul>
        </div>
      </div>

      <!-- Progress Akademik -->
      <div class="card-base">
        <div class="card-header">
          <div class="card-title">
            <span class="header-icon text-blue-600">📊</span>
            <h3>Progress Akademik</h3>
          </div>
        </div>
        <div class="card-body progress-body">
          <div class="chart-container">
            <!-- Simple SVG Donut Chart -->
            <svg viewBox="0 0 36 36" class="donut-chart">
              <path
                class="donut-hole"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="donut-ring"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="donut-segment"
                stroke-dasharray="85, 100"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <text x="18" y="20.35" class="donut-text">85</text>
            </svg>
            <div class="chart-center-text">
              <span class="sks-label" style="display:block; font-size:1.5rem;">Nilai</span>
              <span class="sks-value" style="font-size:0.8rem;">Rata-rata 85</span>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <div class="legend-dot bg-blue-500"></div>
              <span class="legend-label">Ujian Harian</span>
              <span class="legend-value">85</span>
            </div>
            <div class="legend-item">
              <div class="legend-dot bg-purple-500"></div>
              <span class="legend-label">Tugas Sekolah</span>
              <span class="legend-value">90</span>
            </div>
            <div class="legend-item">
              <div class="legend-dot bg-orange-500"></div>
              <span class="legend-label">Tugas Kelompok</span>
              <span class="legend-value">80</span>
            </div>
            <a href="#" class="view-all-link text-center mt-2">Lihat detail progress →</a>
          </div>
        </div>
      </div>

      <!-- Aktivitas Terbaru -->
      <div class="card-base">
        <div class="card-header">
          <div class="card-title">
            <span class="header-icon text-blue-600">🕒</span>
            <h3>Aktivitas Terbaru</h3>
          </div>
        </div>
        <div class="card-body">
          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-icon bg-emerald-100 text-emerald-600">✓</div>
              <div class="activity-content">
                <p class="act-title">Mengikuti Ujian Harian Matematika</p>
                <p class="act-time">Hari ini, 07:00</p>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon bg-blue-100 text-blue-600">📄</div>
              <div class="activity-content">
                <p class="act-title">Mengumpulkan Tugas Kelompok Fisika</p>
                <p class="act-time">Kemarin, 14:32</p>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon bg-purple-100 text-purple-600">📋</div>
              <div class="activity-content">
                <p class="act-title">Meminjam buku di Perpustakaan</p>
                <p class="act-time">1 Sep 2025, 10:15</p>
              </div>
            </div>
            <div class="activity-item">
              <div class="activity-icon bg-orange-100 text-orange-600">💰</div>
              <div class="activity-content">
                <p class="act-title">Membayar uang Kas Kelas</p>
                <p class="act-time">28 Agu 2025, 16:20</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Motivasi / Quote -->
      <div class="card-base quote-card bg-blue-50">
        <div class="quote-content">
          <span class="quote-icon text-blue-400">🎓</span>
          <p class="quote-text">
            "Jangan berhenti belajar, karena ilmu adalah investasi terbaik untuk masa depanmu."
          </p>
        </div>
        <div class="quote-illustration">
          <svg viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="books-svg">
            <rect x="20" y="60" width="60" height="15" fill="#3b82f6" rx="2" />
            <rect x="25" y="45" width="50" height="15" fill="#8b5cf6" rx="2" />
            <rect x="30" y="30" width="40" height="15" fill="#14b8a6" rx="2" />
          </svg>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const user = ref({});

onMounted(() => {
  try {
    user.value = JSON.parse(localStorage.getItem('user') || '{}');
  } catch (e) {
    user.value = {};
  }
});
</script>

<style scoped>
/* Utilities */
.text-blue-600 { color: #2563eb; }
.bg-blue-100 { background-color: #dbeafe; }
.text-emerald-600 { color: #059669; }
.bg-emerald-100 { background-color: #d1fae5; }
.text-purple-600 { color: #9333ea; }
.bg-purple-100 { background-color: #f3e8ff; }
.text-green-600 { color: #16a34a; }
.bg-green-100 { background-color: #dcfce7; }
.text-orange-600 { color: #ea580c; }
.bg-orange-100 { background-color: #ffedd5; }
.text-green-500 { color: #22c55e; }
.text-gray-500 { color: #6b7280; }
.font-medium { font-weight: 500; }
.bg-blue-500 { background-color: #3b82f6; }
.bg-purple-500 { background-color: #a855f7; }
.bg-orange-500 { background-color: #f97316; }

.dashboard-home {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Welcome Banner */
.welcome-banner {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  border-radius: 1.25rem;
  padding: 2rem 2.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15);
  position: relative;
  overflow: hidden;
}

.banner-content {
  z-index: 2;
}

.welcome-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e3a8a;
  margin: 0 0 0.5rem 0;
}

.welcome-subtitle {
  font-size: 1rem;
  color: #3b82f6;
  margin: 0 0 1.25rem 0;
}

.semester-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.8);
  color: #1e3a8a;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
}

.banner-illustration {
  width: 250px;
  height: auto;
  z-index: 1;
}

.building-svg {
  width: 100%;
  height: 100%;
}

/* Stat Cards Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 1.5rem;
}

.stat-card {
  background: #ffffff;
  border-radius: 1.25rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
}

.stat-icon-wrap {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 0.25rem 0;
  font-weight: 500;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
  line-height: 1.1;
}

.stat-value-sub {
  font-size: 1rem;
  color: #94a3b8;
  font-weight: 500;
}

.stat-sub {
  font-size: 0.75rem;
  margin: 0;
}

.progress-bar-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.35rem;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: #10b981;
  border-radius: 999px;
  position: relative;
}

.progress-bar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: -64%;
  height: 100%;
  background: #e2e8f0;
  border-radius: 999px;
  z-index: -1;
}

.progress-text {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

/* Main Grid */
.main-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.col-span-2 {
  grid-column: span 2;
}

.card-base {
  background: #ffffff;
  border-radius: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
}

.card-header {
  padding: 1.5rem 1.5rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-title h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Schedule Table */
.schedule-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
}

.schedule-table th {
  text-align: left;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.schedule-table th:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
.schedule-table th:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

.schedule-table td {
  padding: 1rem;
  font-size: 0.9rem;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.schedule-table tr:last-child td {
  border-bottom: none;
}

.schedule-table tr:hover td {
  background-color: #f8fafc;
}

.view-all-link {
  display: inline-block;
  color: #3b82f6;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  margin-top: auto;
  transition: color 0.2s;
}

.view-all-link:hover {
  color: #2563eb;
  text-decoration: underline;
}

/* Announcements */
.announcement-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.announcement-item {
  position: relative;
  padding-left: 1rem;
}

.announcement-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.35rem;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.border-l-blue::before { background-color: #3b82f6; }
.border-l-orange::before { background-color: #f97316; }
.border-l-green::before { background-color: #10b981; }
.border-l-purple::before { background-color: #a855f7; }

.announce-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.25rem;
}

.announce-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  padding-right: 1rem;
}

.announce-date {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
}

.announce-desc {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
  line-height: 1.4;
}

/* Progress Akademik */
.progress-body {
  flex-direction: row;
  align-items: center;
  gap: 2rem;
}

.chart-container {
  width: 140px;
  height: 140px;
  position: relative;
}

.donut-chart {
  width: 100%;
  height: 100%;
}

.donut-hole {
  fill: #fff;
}

.donut-ring {
  fill: transparent;
  stroke: #e2e8f0;
  stroke-width: 3.5;
}

.donut-segment {
  fill: transparent;
  stroke: #3b82f6;
  stroke-width: 3.5;
  stroke-linecap: round;
}

.donut-text {
  fill: transparent; /* hide text in svg, using html overlay */
}

.chart-center-text {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.chart-center-text .sks-label {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
}

.chart-center-text .sks-label {
  display: none;
}

.chart-container::after {
  display: none;
}

.chart-container::before {
  display: none;
}

.chart-legend {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.legend-item {
  display: flex;
  align-items: center;
  font-size: 0.85rem;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 0.5rem;
}

.legend-label {
  color: #475569;
  flex: 1;
}

.legend-value {
  font-weight: 600;
  color: #1e293b;
}

/* Aktivitas Terbaru */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.activity-item {
  display: flex;
  gap: 1rem;
}

.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.act-title {
  font-size: 0.85rem;
  color: #334155;
  margin: 0 0 0.15rem 0;
  line-height: 1.3;
}

.act-time {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
}

/* Quote Card */
.quote-card {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 1.5rem;
}

.quote-content {
  z-index: 2;
  position: relative;
}

.quote-icon {
  font-size: 2rem;
  opacity: 0.5;
  margin-bottom: 0.5rem;
  display: block;
}

.quote-text {
  font-size: 1rem;
  font-style: italic;
  color: #1e3a8a;
  line-height: 1.5;
  margin: 0;
  font-weight: 500;
}

.quote-illustration {
  position: absolute;
  right: -10px;
  bottom: -10px;
  width: 120px;
  opacity: 0.8;
  z-index: 1;
}

.books-svg {
  width: 100%;
  height: auto;
}

/* Responsive */
@media (max-width: 1280px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .main-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .main-grid {
    grid-template-columns: 1fr;
  }
  .col-span-2 {
    grid-column: span 1;
  }
  .welcome-banner {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem;
  }
  .banner-illustration {
    margin-top: 1rem;
    width: 200px;
  }
  .progress-body {
    flex-direction: column;
  }
}
</style>
