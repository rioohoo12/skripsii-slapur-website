const API_BASE = import.meta.env.VITE_API_URL || '/api';

function getToken() {
  return localStorage.getItem('auth_token');
}

async function request(url, options = {}) {
  const path = url.startsWith('/') ? url : `/${url}`;
  const fullUrl = `${API_BASE.replace(/\/$/, '')}${path}`;
  const headers = {
    Accept: 'application/json',
    ...(options.headers || {}),
  };
  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }
  if (getToken()) headers.Authorization = `Bearer ${getToken()}`;

  const res = await fetch(fullUrl, { ...options, headers });
  const text = await res.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    // abaikan JSON parse error, data akan kosong
  }
  if (!res.ok) {
    const err = new Error(data.message || `Error ${res.status}`);
    err.status = res.status;
    throw err;
  }
  return data;
}

export const guruApi = {
  async getKelas() {
    return request('/guru/kelas');
  },
  async getKelasDetail(tingkat) {
    return request(`/guru/kelas/${tingkat}`);
  },
};

