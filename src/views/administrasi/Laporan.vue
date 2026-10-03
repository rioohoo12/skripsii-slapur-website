<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
    <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-gray-800">Laporan Ekspor</h2>
        <p class="text-sm text-gray-500">Buat laporan pembayaran atau progres pendaftaran ke format PDF / Excel.</p>
      </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Card Laporan Pembayaran -->
      <div class="border border-gray-200 rounded-xl p-6 bg-gray-50 hover:border-blue-300 transition">
        <h3 class="font-bold text-lg text-gray-800 mb-2">Laporan Pembayaran</h3>
        <p class="text-sm text-gray-600 mb-4">Laporan rincian uang masuk, pembayaran berhasil berdasarkan rentang tanggal.</p>
        
        <div class="space-y-3">
          <div class="flex gap-2">
            <input type="date" v-model="startDate" class="px-3 py-2 border rounded w-full text-sm">
            <input type="date" v-model="endDate" class="px-3 py-2 border rounded w-full text-sm">
          </div>
          <div class="flex gap-2">
            <button @click="download('pembayaran', 'pdf')" class="flex-1 bg-red-600 text-white rounded py-2 text-sm font-medium hover:bg-red-700">📄 PDF</button>
            <button @click="download('pembayaran', 'excel')" class="flex-1 bg-green-600 text-white rounded py-2 text-sm font-medium hover:bg-green-700">📊 Excel</button>
          </div>
        </div>
      </div>

      <!-- Card Laporan Dokumen -->
      <div class="border border-gray-200 rounded-xl p-6 bg-gray-50 hover:border-blue-300 transition">
        <h3 class="font-bold text-lg text-gray-800 mb-2">Laporan Dokumen & Pendaftar</h3>
        <p class="text-sm text-gray-600 mb-4">Laporan daftar siswa yang belum melengkapi dokumen atau belum menyelesaikan pendaftaran.</p>
        
        <div class="space-y-3 mt-12">
          <div class="flex gap-2">
            <button @click="download('dokumen', 'pdf')" class="flex-1 bg-red-600 text-white rounded py-2 text-sm font-medium hover:bg-red-700">📄 PDF</button>
            <button @click="download('dokumen', 'excel')" class="flex-1 bg-green-600 text-white rounded py-2 text-sm font-medium hover:bg-green-700">📊 Excel</button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const startDate = ref('');
const endDate = ref('');

const download = (type, format) => {
  alert(`Memulai pembuatan laporan ${type} dalam format ${format.toUpperCase()}...`);
  // Logika export sesungguhnya memerlukan pustaka backend (e.g. dompdf atau maatwebsite/excel)
  // Untuk saat ini, trigger notifikasi mock.
};
</script>
