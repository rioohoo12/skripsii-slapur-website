<template>
  <div class="bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-200/80 rounded-xl p-3.5 my-2 shadow-xs flex flex-col gap-2.5">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs">
        ⚡
      </div>
      <div>
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ cardTitle }}</h4>
        <p class="text-xs text-slate-600">{{ cardSubtitle }}</p>
      </div>
    </div>

    <!-- Action Details -->
    <div v-if="slotsData && Object.keys(slotsData).length" class="text-xs bg-white/80 p-2.5 rounded-lg border border-teal-100 flex flex-col gap-1">
      <div v-for="(val, key) in displaySlots" :key="key" class="flex justify-between">
        <span class="text-slate-500 capitalize">{{ formatKey(key) }}:</span>
        <span class="font-semibold text-slate-700">{{ val }}</span>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2 mt-1">
      <button 
        v-if="type === 'pembayaran' || type === 'payment'"
        @click="handleAction('pembayaran')"
        class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs py-2 px-3 rounded-lg transition-colors shadow-xs flex items-center justify-center gap-1.5"
      >
        <span>💳</span> Bayar Sekarang
      </button>

      <button 
        v-if="type === 'pendaftaran' || type === 'register'"
        @click="handleAction('pendaftaran')"
        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs py-2 px-3 rounded-lg transition-colors shadow-xs flex items-center justify-center gap-1.5"
      >
        <span>📝</span> Lanjutkan Formulir
      </button>

      <button 
        @click="handleAction('staf')"
        class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-medium text-xs py-2 px-3 rounded-lg transition-colors"
      >
        Hubungi Staf
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  type: {
    type: String,
    default: 'pendaftaran',
  },
  slotsData: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['action-click']);
const router = useRouter();

const cardTitle = computed(() => {
  if (props.type === 'pembayaran' || props.type === 'payment') return 'Invoice Pembayaran';
  return 'Bantuan Pendaftaran';
});

const cardSubtitle = computed(() => {
  if (props.type === 'pembayaran' || props.type === 'payment') return 'Konfirmasi pembuatan tagihan virtual account';
  return 'Lengkapi data pendaftaran otomatis';
});

const displaySlots = computed(() => {
  const result = {};
  for (const [k, v] of Object.entries(props.slotsData)) {
    if (v && v !== 'belum diisi' && !k.startsWith('_')) {
      result[k] = v;
    }
  }
  return result;
});

function formatKey(key) {
  return key.replace(/_/g, ' ');
}

function handleAction(actionType) {
  if (actionType === 'pendaftaran') {
    router.push('/siswa/laki-laki/pendaftaran');
  } else if (actionType === 'pembayaran') {
    router.push('/siswa/laki-laki/pendaftaran/pembayaran');
  }
  emit('action-click', { action: actionType });
}
</script>
