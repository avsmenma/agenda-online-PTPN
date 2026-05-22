@extends('layouts.app')

@section('content')
<style>
  .va-page {
    background: #f4f6fb;
    color: #1a2340;
    font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
    height: calc(100dvh - 56px);
    min-height: 680px;
    overflow: hidden;
    padding: 22px 28px 18px;
  }

  .va-shell {
    display: grid;
    gap: 18px;
    grid-template-rows: auto minmax(0, 1fr);
    height: 100%;
    min-height: 0;
  }

  .va-header {
    align-items: flex-start;
    display: flex;
    gap: 18px;
    justify-content: space-between;
  }

  .va-title {
    color: #14213d;
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 22px;
    font-weight: 750;
    margin: 0;
  }

  .va-subtitle {
    color: #8a99b5;
    font-size: 13px;
    margin-top: 5px;
  }

  .va-new-chat {
    align-items: center;
    background: #ffffff;
    border: 1px solid #dce5f0;
    border-radius: 10px;
    color: #566681;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    gap: 8px;
    min-height: 40px;
    padding: 0 14px;
  }

  .va-chat-card {
    background: #ffffff;
    border: 1px solid #e8ecf4;
    border-radius: 18px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
    display: grid;
    grid-template-rows: minmax(0, 1fr) auto;
    height: 100%;
    min-height: 0;
    overflow: hidden;
  }

  .va-messages {
    background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
    min-height: 0;
    overflow-y: auto;
    padding: 24px 20px;
  }

  .va-empty {
    align-items: center;
    color: #7d8ba6;
    display: flex;
    flex-direction: column;
    gap: 10px;
    height: 100%;
    justify-content: center;
    min-height: 360px;
    text-align: center;
  }

  .va-empty-icon {
    align-items: center;
    background: linear-gradient(135deg, #0f766e, #10b981);
    border-radius: 18px;
    color: #ffffff;
    display: flex;
    height: 56px;
    justify-content: center;
    width: 56px;
  }

  .va-empty-title {
    color: #14213d;
    font-size: 16px;
    font-weight: 800;
  }

  .va-message {
    display: flex;
    margin-bottom: 16px;
  }

  .va-message.user {
    justify-content: flex-end;
  }

  .va-bubble {
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.55;
    max-width: min(760px, 88%);
    padding: 13px 15px;
  }

  .va-message.user .va-bubble {
    background: #0f766e;
    color: #ffffff;
    border-bottom-right-radius: 4px;
  }

  .va-message.assistant .va-bubble {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    color: #1f2937;
  }

  .va-answer {
    font-weight: 650;
    margin-bottom: 10px;
    white-space: pre-wrap;
  }

  .va-doc-list,
  .va-metric-list {
    display: grid;
    gap: 8px;
    margin-top: 10px;
  }

  .va-doc-card,
  .va-metric-card {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 10px 11px;
  }

  .va-doc-head {
    align-items: center;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin-bottom: 4px;
  }

  .va-doc-no {
    color: #0f766e;
    font-weight: 850;
  }

  .va-doc-value {
    color: #14213d;
    font-size: 12px;
    font-weight: 850;
    white-space: nowrap;
  }

  .va-doc-desc {
    color: #26344d;
    font-weight: 650;
    margin-bottom: 7px;
  }

  .va-doc-meta {
    color: #6b7a99;
    display: flex;
    flex-wrap: wrap;
    font-size: 11.5px;
    gap: 7px 12px;
  }

  .va-result-link {
    align-items: center;
    color: #2563eb;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 6px;
    margin-top: 12px;
    text-decoration: none;
  }

  .va-copy {
    background: transparent;
    border: 0;
    color: #7d8ba6;
    font-size: 12px;
    font-weight: 700;
    margin-top: 10px;
    padding: 0;
  }

  .va-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
  }

  .va-feedback {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #dce5f0;
    border-radius: 999px;
    color: #53627c;
    display: inline-flex;
    font-size: 11.5px;
    font-weight: 800;
    gap: 5px;
    min-height: 30px;
    padding: 0 10px;
  }

  .va-feedback:hover,
  .va-feedback.saved {
    background: #ecfdf5;
    border-color: #99f6e4;
    color: #0f766e;
  }

  .va-feedback.negative:hover,
  .va-feedback.negative.saved {
    background: #fff1f2;
    border-color: #fecdd3;
    color: #be123c;
  }

  .va-feedback-status {
    color: #7d8ba6;
    font-size: 11.5px;
    font-weight: 700;
  }

  .va-loading {
    align-items: center;
    color: #66758f;
    display: inline-flex;
    gap: 8px;
  }

  .va-dot {
    animation: vaPulse 1s infinite ease-in-out;
    background: #10b981;
    border-radius: 999px;
    height: 7px;
    width: 7px;
  }

  .va-dot:nth-child(2) { animation-delay: 0.15s; }
  .va-dot:nth-child(3) { animation-delay: 0.3s; }

  @keyframes vaPulse {
    0%, 100% { opacity: 0.3; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-2px); }
  }

  .va-input-wrap {
    background: #ffffff;
    border-top: 1px solid #eef2f7;
    padding: 10px 16px 12px;
    z-index: 5;
  }

  .va-input-box {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #dce5f0;
    border-radius: 13px;
    display: flex;
    gap: 10px;
    padding: 7px 8px 7px 12px;
  }

  .va-input {
    background: transparent;
    border: 0;
    color: #14213d;
    flex: 1;
    font-size: 13.5px;
    line-height: 1.45;
    max-height: 96px;
    min-height: 30px;
    outline: none;
    resize: none;
  }

  .va-send {
    align-items: center;
    background: #0f766e;
    border: 0;
    border-radius: 11px;
    color: #ffffff;
    display: inline-flex;
    font-size: 13px;
    font-weight: 800;
    gap: 8px;
    min-height: 38px;
    padding: 0 15px;
  }

  .va-send:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
  }

  .va-input-help {
    color: #8a99b5;
    font-size: 10.5px;
    margin-top: 7px;
  }

  @media (max-width: 768px) {
    .va-page {
      height: calc(100dvh - 72px);
      min-height: 520px;
      padding: 16px 12px 12px;
    }

    .va-header {
      flex-direction: column;
    }

    .va-bubble {
      max-width: 96%;
    }

    .va-input-box {
      align-items: center;
      flex-direction: row;
    }

    .va-send {
      justify-content: center;
      width: auto;
    }
  }
</style>

<div class="va-page">
  <div class="va-shell">
    <div class="va-header">
      <div>
        <h1 class="va-title">Asisten Virtual</h1>
        <div class="va-subtitle">Tanyakan data dokumen, pembayaran, status, dan laporan kepada asisten AI</div>
      </div>
      <button class="va-new-chat" type="button" id="vaNewChat">
        <i class="fa-solid fa-plus"></i>
        Chat Baru
      </button>
    </div>

    <section class="va-chat-card" aria-label="Chat Asisten Virtual">
      <div class="va-messages" id="vaMessages">
        <div class="va-empty" id="vaEmpty">
          <div class="va-empty-icon">
            <i class="fa-solid fa-robot"></i>
          </div>
          <div class="va-empty-title">Mulai tanya data Agenda Online</div>
          <div>Asisten hanya membaca data melalui query aman yang sudah dibatasi aplikasi.</div>
        </div>
      </div>

      <form class="va-input-wrap" id="vaForm">
        <div class="va-input-box">
          <textarea class="va-input" id="vaInput" name="message" maxlength="{{ config('asisten_virtual.limits.max_message_length', 800) }}" placeholder="Contoh: Tampilkan dokumen yang belum dibayar di atas 100 juta"></textarea>
          <button class="va-send" type="submit" id="vaSend">
            <i class="fa-solid fa-paper-plane"></i>
            Kirim
          </button>
        </div>
        <div class="va-input-help">Enter untuk kirim, Shift + Enter untuk baris baru. Hasil besar akan dibatasi agar halaman tetap ringan.</div>
      </form>
    </section>
  </div>
</div>

<script>
(() => {
  const endpoint = @json(route('owner.asisten-virtual.chat'));
  const feedbackEndpointTemplate = @json(route('owner.asisten-virtual.feedback', ['interaction' => '__ID__']));
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const messages = document.getElementById('vaMessages');
  const empty = document.getElementById('vaEmpty');
  const form = document.getElementById('vaForm');
  const input = document.getElementById('vaInput');
  const send = document.getElementById('vaSend');
  const newChat = document.getElementById('vaNewChat');
  const currentUserId = @json(auth()->id());
  const storageKey = `virtual_assistant_chat_${currentUserId || 'guest'}`;
  const maxStoredMessages = 80;
  let loadingNode = null;
  let conversationContext = {};
  let chatHistory = [];

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  function scrollToBottom(force = false) {
    const distanceFromBottom = messages.scrollHeight - messages.scrollTop - messages.clientHeight;
    if (!force && distanceFromBottom > 140) return;
    messages.scrollTo({ top: messages.scrollHeight, behavior: force ? 'auto' : 'smooth' });
  }

  function ensureStarted() {
    if (empty) empty.style.display = 'none';
  }

  function saveChatHistory() {
    try {
      const trimmed = chatHistory.slice(-maxStoredMessages);
      chatHistory = trimmed;
      localStorage.setItem(storageKey, JSON.stringify({
        version: 1,
        user_id: currentUserId,
        saved_at: new Date().toISOString(),
        messages: trimmed,
        context: conversationContext,
      }));
    } catch (error) {
      // Storage can fail in private mode or when the quota is full. Chat still works without persistence.
    }
  }

  function rememberMessage(entry) {
    chatHistory.push(entry);
    saveChatHistory();
  }

  function addUserMessage(text, options = {}) {
    const { persist = true, scroll = true } = options;
    ensureStarted();
    const row = document.createElement('div');
    row.className = 'va-message user';
    row.innerHTML = `<div class="va-bubble">${escapeHtml(text)}</div>`;
    messages.appendChild(row);
    if (persist) rememberMessage({ role: 'user', text });
    if (scroll) scrollToBottom(true);
  }

  function addLoading() {
    ensureStarted();
    loadingNode = document.createElement('div');
    loadingNode.className = 'va-message assistant';
    loadingNode.innerHTML = `
      <div class="va-bubble">
        <span class="va-loading">
          <span>Asisten sedang menganalisis data</span>
          <span class="va-dot"></span><span class="va-dot"></span><span class="va-dot"></span>
        </span>
      </div>`;
    messages.appendChild(loadingNode);
    scrollToBottom(true);
  }

  function removeLoading() {
    if (loadingNode) loadingNode.remove();
    loadingNode = null;
  }

  function renderData(data) {
    if (!data || (Array.isArray(data) && data.length === 0)) return '';

    if (Array.isArray(data)) {
      return `<div class="va-doc-list">${data.map((item) => {
        if (item.nomor_agenda || item.nomor_spp || item.uraian) {
          return `
            <div class="va-doc-card">
              <div class="va-doc-head">
                <span class="va-doc-no">${escapeHtml(item.nomor_agenda || item.nomor_spp || '-')}</span>
                <span class="va-doc-value">${escapeHtml(item.nilai || '')}</span>
              </div>
              <div class="va-doc-desc">${escapeHtml(item.uraian || '-')}</div>
              <div class="va-doc-meta">
                <span>Bagian: ${escapeHtml(item.bagian || '-')}</span>
                <span>Status: ${escapeHtml(item.status || '-')}</span>
                <span>Pembayaran: ${escapeHtml(item.status_pembayaran || '-')}</span>
                <span>Pengurus: ${escapeHtml(item.pengurus || '-')}</span>
                <span>Tanggal: ${escapeHtml(item.tanggal_masuk || '-')}</span>
                ${item.umur ? `<span>Umur: ${escapeHtml(item.umur)}</span>` : ''}
              </div>
            </div>`;
        }

        return `
          <div class="va-metric-card">
            ${Object.entries(item).map(([key, value]) => `<div><strong>${escapeHtml(key.replaceAll('_', ' '))}:</strong> ${escapeHtml(value)}</div>`).join('')}
          </div>`;
      }).join('')}</div>`;
    }

    if (typeof data === 'object') {
      return `<div class="va-metric-list"><div class="va-metric-card">${Object.entries(data)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => `<div><strong>${escapeHtml(key.replaceAll('_', ' '))}:</strong> ${escapeHtml(value)}</div>`)
        .join('')}</div></div>`;
    }

    return '';
  }

  function compactStoredReply(reply) {
    return {
      intent: reply?.intent || null,
      answer: reply?.answer || '',
      data: Array.isArray(reply?.data) ? reply.data.slice(0, 20) : (reply?.data || []),
      link: reply?.link || null,
      interaction_id: reply?.interaction_id || null,
    };
  }

  function addAssistantMessage(reply, options = {}) {
    const { persist = true, scroll = true } = options;
    ensureStarted();
    const row = document.createElement('div');
    row.className = 'va-message assistant';
    const answer = reply?.answer || 'Maaf, saya belum mendapat jawaban.';
    const dataHtml = renderData(reply?.data);
    const linkHtml = reply?.link ? `<a class="va-result-link" href="${escapeHtml(reply.link)}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat dokumen terkait</a>` : '';
    const feedbackHtml = reply?.interaction_id ? `
      <div class="va-actions" data-feedback-for="${escapeHtml(reply.interaction_id)}">
        <button class="va-feedback" type="button" data-feedback="helpful"><i class="fa-solid fa-thumbs-up"></i> Membantu</button>
        <button class="va-feedback negative" type="button" data-feedback="not_helpful"><i class="fa-solid fa-thumbs-down"></i> Tidak sesuai</button>
        <button class="va-feedback negative" type="button" data-feedback="wrong_answer"><i class="fa-solid fa-triangle-exclamation"></i> Laporkan jawaban salah</button>
        <span class="va-feedback-status" aria-live="polite"></span>
      </div>` : '';
    row.innerHTML = `
      <div class="va-bubble">
        <div class="va-answer">${escapeHtml(answer)}</div>
        ${dataHtml}
        ${linkHtml}
        <div class="va-actions">
          <button class="va-copy" type="button">Copy jawaban</button>
        </div>
        ${feedbackHtml}
      </div>`;
    row.querySelector('.va-copy')?.addEventListener('click', () => navigator.clipboard?.writeText(answer));
    row.querySelectorAll('.va-feedback').forEach((button) => {
      button.addEventListener('click', () => sendFeedback(button, reply.interaction_id));
    });
    messages.appendChild(row);
    updateConversationContext(reply);
    if (persist) rememberMessage({ role: 'assistant', reply: compactStoredReply(reply) });
    if (scroll) scrollToBottom(true);
  }

  async function sendFeedback(button, interactionId) {
    if (!interactionId || button.disabled) return;

    const feedback = button.dataset.feedback;
    const container = button.closest('[data-feedback-for]');
    const status = container?.querySelector('.va-feedback-status');
    const reason = '';

    container?.querySelectorAll('.va-feedback').forEach((item) => {
      item.disabled = true;
    });
    if (status) status.textContent = 'Menyimpan feedback...';

    try {
      const url = feedbackEndpointTemplate.replace('__ID__', encodeURIComponent(interactionId));
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ feedback, reason }),
      });
      const payload = await response.json().catch(() => ({}));

      if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Feedback gagal disimpan.');
      }

      button.classList.add('saved');
      if (status) status.textContent = payload.message || 'Feedback tersimpan.';
    } catch (error) {
      container?.querySelectorAll('.va-feedback').forEach((item) => {
        item.disabled = false;
      });
      if (status) status.textContent = 'Feedback gagal disimpan.';
    }
  }

  function updateConversationContext(reply) {
    const data = Array.isArray(reply?.data) ? reply.data : [];
    const documentRows = data.filter((item) => item && (item.id || item.nomor_agenda || item.nomor_spp));

    conversationContext.last_intent = reply?.intent || null;
    conversationContext.last_link = reply?.link || null;
    conversationContext.last_documents = documentRows.slice(0, 5).map((item) => ({
      id: item.id || null,
      nomor_agenda: item.nomor_agenda || null,
      nomor_spp: item.nomor_spp || null,
      status_pembayaran: item.status_pembayaran || null,
      status: item.status || null,
    }));

    conversationContext.selected_document = documentRows.length === 1
      ? conversationContext.last_documents[0]
      : null;
  }

  function restoreChatHistory() {
    let saved = null;

    try {
      saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
    } catch (error) {
      saved = null;
    }

    if (!saved || !Array.isArray(saved.messages) || saved.messages.length === 0) {
      return;
    }

    chatHistory = saved.messages.slice(-maxStoredMessages);
    conversationContext = saved.context && typeof saved.context === 'object' ? saved.context : {};
    chatHistory.forEach((entry) => {
      if (entry?.role === 'user') {
        addUserMessage(entry.text || '', { persist: false, scroll: false });
      } else if (entry?.role === 'assistant') {
        addAssistantMessage(entry.reply || {}, { persist: false, scroll: false });
      }
    });
    scrollToBottom(true);
  }

  function clearStoredChat() {
    chatHistory = [];
    conversationContext = {};
    try {
      localStorage.removeItem(storageKey);
    } catch (error) {
      // Ignore storage failures.
    }
  }

  function resizeInput() {
    input.style.height = 'auto';
    input.style.height = `${Math.min(input.scrollHeight, 96)}px`;
  }

  function setLoading(isLoading) {
    send.disabled = isLoading;
    input.disabled = isLoading;
    if (isLoading) addLoading();
    else removeLoading();
  }

  async function submitMessage(text) {
    const message = text.trim();
    if (!message) return;

    addUserMessage(message);
    input.value = '';
    resizeInput();
    setLoading(true);

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({
          message,
          context: conversationContext,
        }),
      });
      const payload = await response.json().catch(() => ({}));
      removeLoading();

      if (!response.ok || !payload.success) {
        addAssistantMessage({ answer: payload.message || 'Maaf, terjadi kendala saat memproses pertanyaan.' });
        return;
      }

      addAssistantMessage(payload.reply);
    } catch (error) {
      removeLoading();
      addAssistantMessage({ answer: 'Maaf, koneksi ke Asisten Virtual gagal. Silakan coba lagi.' });
    } finally {
      setLoading(false);
      input.focus();
    }
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    submitMessage(input.value);
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  input.addEventListener('input', resizeInput);

  newChat.addEventListener('click', () => {
    messages.innerHTML = '';
    messages.appendChild(empty);
    empty.style.display = 'flex';
    clearStoredChat();
    input.value = '';
    resizeInput();
    input.focus();
  });

  restoreChatHistory();
  resizeInput();
})();
</script>
@endsection
