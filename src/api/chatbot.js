const API_BASE = import.meta.env.VITE_API_URL || '/api';

async function request(url, options = {}) {
  const path = url.startsWith('/') ? url : `/${url}`;
  const fullUrl = `${API_BASE.replace(/\/$/, '')}${path}`;
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(options.headers || {}),
  };
  const token = localStorage.getItem('auth_token');
  if (token) headers.Authorization = `Bearer ${token}`;

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

export const chatbotApi = {
  async sendMessage(sessionId, message) {
    return request('/chatbot/message', {
      method: 'POST',
      body: JSON.stringify({ session_id: sessionId || undefined, message }),
    });
  },
  async getHistory(sessionId) {
    if (!sessionId) return { messages: [] };
    return request(`/chatbot/history?session_id=${encodeURIComponent(sessionId)}`);
  },
  async clearChat(sessionId) {
    return request('/chatbot/clear', {
      method: 'POST',
      body: JSON.stringify({ session_id: sessionId || undefined }),
    });
  },
};
