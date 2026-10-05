const API_BASE = import.meta.env.VITE_API_URL || '/api';

async function request(url, options = {}) {
  const path = url.startsWith('/') ? url : `/${url}`;
  const fullUrl = `${API_BASE.replace(/\/$/, '')}${path}`;
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(options.headers || {}),
  };
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const res = await fetch(fullUrl, { ...options, headers });
  const text = await res.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch (_) {}

  if (!res.ok) {
    const err = new Error(data.message || data.reply || `Error ${res.status}`);
    err.status = res.status;
    err.data = data;
    throw err;
  }
  return data;
}

export const chatbotApi = {
  // Main Endpoint Sanctum Architecture
  async sendMessage(sessionId, message) {
    return request('/chatbot/message', {
      method: 'POST',
      body: JSON.stringify({
        session_id: sessionId || undefined,
        message,
      }),
    });
  },

  async getHistory(sessionId) {
    if (!sessionId) return { messages: [] };
    return request(`/chatbot/history?session_id=${encodeURIComponent(sessionId)}`);
  },

  async confirmAction(sessionId, action, data = {}) {
    return request('/chatbot/action/confirm', {
      method: 'POST',
      body: JSON.stringify({
        session_id: sessionId || undefined,
        action,
        data,
      }),
    });
  },

  async clearChat(sessionId) {
    return request('/chatbot/clear', {
      method: 'POST',
      body: JSON.stringify({ session_id: sessionId || undefined }),
    });
  },

  // Fallback V2 Endpoint compatibility
  async sendMessageV2(sessionId, message) {
    return request('/v2/chat/message', {
      method: 'POST',
      body: JSON.stringify({ session_id: sessionId, message }),
    });
  }
};
