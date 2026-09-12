const API_BASE = import.meta.env.VITE_API_URL || '/api';

function getToken() {
  return localStorage.getItem('auth_token');
}

async function request(url, options = {}) {
  const path = url.startsWith('/') ? url : `/${url}`;
  const fullUrl = `${API_BASE.replace(/\/$/, '')}${path}`;
  const isFormData = options.body instanceof FormData;
  const headers = {
    Accept: 'application/json',
    ...(options.headers || {}),
  };
  if (!isFormData) headers['Content-Type'] = 'application/json';
  if (getToken()) headers.Authorization = `Bearer ${getToken()}`;

  const res = await fetch(fullUrl, { ...options, headers });
  const text = await res.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch (_) {}
  if (!res.ok) {
    const err = new Error(data.message || `Error ${res.status}`);
    err.status = res.status;
    throw err;
  }
  return data;
}

export const pendaftaranApi = {
  async getStatus() {
    return request('/pendaftaran/status');
  },
  async getDokumen() {
    return request('/pendaftaran/dokumen');
  },
  async getKurikulum(tingkat) {
    const q = tingkat != null ? `?tingkat=${tingkat}` : '';
    return request(`/pendaftaran/kurikulum${q}`);
  },
  async getKamar() {
    return request('/pendaftaran/kamar');
  },
  async uploadBuktiPembayaran(file) {
    const form = new FormData();
    form.append('file', file);
    return request('/pendaftaran/upload-bukti-pembayaran', {
      method: 'POST',
      body: form,
    });
  },
  async uploadDokumen(jenis, file) {
    const form = new FormData();
    form.append('jenis', jenis);
    form.append('file', file);
    return request('/pendaftaran/upload-dokumen', {
      method: 'POST',
      body: form,
    });
  },
};
