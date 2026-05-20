@extends('layouts.app')

@section('content')
<style>
  .va-page {
    background: #f4f6fb;
    color: #1a2340;
    font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
    height: calc(100vh - 104px);
    min-height: 620px;
    overflow: hidden;
    padding: 24px 28px 28px;
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
    grid-template-rows: auto 1fr auto;
    height: 100%;
    min-height: 0;
    overflow: hidden;
  }

  .va-prompts {
    border-bottom: 1px solid #eef2f7;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 14px 16px;
  }

  .va-prompt {
    background: #f8fafc;
    border: 1px solid #dce5f0;
    border-radius: 999px;
    color: #40506c;
    flex: 0 0 auto;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 12px;
  }

  .va-prompt:hover {
    background: #ecfdf5;
    border-color: #bbf7d0;
    color: #0f766e;
  }

  .va-messages {
    background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
    min-height: 0;
    overflow-y: auto;
    padding: 22px 18px;
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
    padding: 14px 16px 16px;
    position: sticky;
    bottom: 0;
    z-index: 5;
  }

  .va-input-box {
    align-items: flex-end;
    background: #f8fafc;
    border: 1px solid #dce5f0;
    border-radius: 14px;
    display: flex;
    gap: 10px;
    padding: 10px;
  }

  .va-input {
    background: transparent;
    border: 0;
    color: #14213d;
    flex: 1;
    font-size: 13.5px;
    line-height: 1.5;
    max-height: 140px;
    min-height: 42px;
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
    min-height: 42px;
    padding: 0 16px;
  }

  .va-send:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
  }

  .va-input-help {
    color: #8a99b5;
    font-size: 11px;
    margin-top: 8px;
  }

  @media (max-width: 768px) {
    .va-page {
      height: calc(100vh - 84px);
      min-height: 520px;
      padding: 18px 14px;
    }

    .va-header {
      flex-direction: column;
    }

    .va-bubble {
      max-width: 96%;
    }

    .va-input-box {
      align-items: stretch;
      flex-direction: column;
    }

    .va-send {
      justify-content: center;
      width: 100%;
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
      <div class="va-prompts" id="vaPrompts">
        <button class="va-prompt" type="button">Dokumen masuk hari ini</button>
        <button class="va-prompt" type="button">Total belum dibayar bulan ini</button>
        <button class="va-prompt" type="button">Dokumen terlambat</button>
        <button class="va-prompt" type="button">Top bagian berdasarkan nilai dokumen</button>
        <button class="va-prompt" type="button">Dokumen pending pembayaran</button>
      </div>

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
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const messages = document.getElementById('vaMessages');
  const empty = document.getElementById('vaEmpty');
  const form = document.getElementById('vaForm');
  const input = document.getElementById('vaInput');
  const send = document.getElementById('vaSend');
  const prompts = document.getElementById('vaPrompts');
  const newChat = document.getElementById('vaNewChat');
  let loadingNode = null;
  let conversationContext = {};

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
  }

  function scrollToBottom() {
    messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
  }

  function ensureStarted() {
    if (empty) empty.style.display = 'none';
  }

  function addUserMessage(text) {
    ensureStarted();
    const row = document.createElement('div');
    row.className = 'va-message user';
    row.innerHTML = `<div class="va-bubble">${escapeHtml(text)}</div>`;
    messages.appendChild(row);
    scrollToBottom();
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
    scrollToBottom();
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

  function addAssistantMessage(reply) {
    ensureStarted();
    const row = document.createElement('div');
    row.className = 'va-message assistant';
    const answer = reply?.answer || 'Maaf, saya belum mendapat jawaban.';
    const dataHtml = renderData(reply?.data);
    const linkHtml = reply?.link ? `<a class="va-result-link" href="${escapeHtml(reply.link)}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat dokumen terkait</a>` : '';
    row.innerHTML = `
      <div class="va-bubble">
        <div class="va-answer">${escapeHtml(answer)}</div>
        ${dataHtml}
        ${linkHtml}
        <button class="va-copy" type="button">Copy jawaban</button>
      </div>`;
    row.querySelector('.va-copy')?.addEventListener('click', () => navigator.clipboard?.writeText(answer));
    messages.appendChild(row);
    updateConversationContext(reply);
    scrollToBottom();
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

  prompts.addEventListener('click', (event) => {
    const prompt = event.target.closest('.va-prompt');
    if (!prompt) return;
    input.value = prompt.textContent.trim();
    form.requestSubmit();
  });

  newChat.addEventListener('click', () => {
    messages.innerHTML = '';
    messages.appendChild(empty);
    empty.style.display = 'flex';
    conversationContext = {};
    input.value = '';
    input.focus();
  });
})();
</script>
@endsection
