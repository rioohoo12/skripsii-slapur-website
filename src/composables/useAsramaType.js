import { computed } from 'vue';
import { useRoute } from 'vue-router';

/**
 * Menentukan konteks asrama staff (putra / putri) berdasarkan route aktif.
 * Route /asramaputra/* -> putra, /asramaputri/* -> putri (lihat meta.asramaType di router).
 */
export function useAsramaType() {
  const route = useRoute();

  const type = computed(() => (route.meta?.asramaType === 'putri' ? 'putri' : 'putra'));
  const isPutri = computed(() => type.value === 'putri');
  const basePath = computed(() => `/asrama${type.value}`);
  const label = computed(() => (isPutri.value ? 'Asrama Putri' : 'Asrama Putra'));
  const gedungPrefix = computed(() => (isPutri.value ? ['D', 'E', 'F'] : ['A', 'B', 'C']));

  /** Tambahkan ?asrama_type=putra|putri ke URL API agar backend memfilter data. */
  const withType = (url) => `${url}${url.includes('?') ? '&' : '?'}asrama_type=${type.value}`;

  return { type, isPutri, basePath, label, gedungPrefix, withType };
}

/** Tentukan asrama milik akun staff (dipakai router guard & login). */
export function getStaffAsramaType(user) {
  if (!user) return null;
  if (user.asrama_type === 'putra' || user.asrama_type === 'putri') return user.asrama_type;
  const email = String(user.email || '').toLowerCase();
  if (email.includes('putri') || user.jenis_kelamin === 'perempuan') return 'putri';
  return 'putra';
}
