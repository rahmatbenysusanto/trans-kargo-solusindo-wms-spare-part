<!-- TKS WMS AI Chat Widget - Floating -->
<div x-data="aiChatWidget" x-show="loaded" x-cloak class="ai-chat-root">

    <!-- Floating Button -->
    <button @click="toggleChat" class="ai-fab" :class="{ 'd-none': isOpen }" title="TKS AI Assistant">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </button>

    <!-- Chat Panel -->
    <div class="ai-chat-panel" x-show="isOpen" x-transition:enter="ai-chat-enter" x-transition:leave="ai-chat-leave">

        {{-- History Drawer Overlay --}}
        <div class="ai-history-overlay" x-show="showSidebar" @click="showSidebar = false" x-transition.opacity></div>

        {{-- History Drawer --}}
        <div class="ai-history-drawer" :class="{ 'open': showSidebar }">
            <div class="ai-history-header">
                <h6 class="mb-0">Riwayat Chat</h6>
                <button @click="showSidebar = false" class="btn btn-sm btn-icon btn-ghost-secondary rounded-circle">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="ai-history-body">
                <button @click="startNewChat(); showSidebar = false" class="ai-new-chat-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg> Chat Baru
                </button>
                <template x-for="conv in conversations" :key="conv.id">
                    <div class="ai-history-item" :class="{ 'active': conv.id === currentConversationId }"
                        @click="loadConversation(conv.id); showSidebar = false">
                        <div class="ai-history-item-title" x-text="conv.title"></div>
                        <div class="ai-history-item-meta">
                            <small x-text="conv.updated_at"></small>
                            <button @click.stop="deleteConversation(conv.id)" class="ai-history-delete" title="Hapus">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="conversations.length === 0" class="text-center text-muted py-5">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin:0 auto 8px"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8"/><path d="M8 13h6"/></svg>
                    <small>Belum ada percakapan</small>
                </div>
            </div>
        </div>

        {{-- Header --}}
        <div class="ai-header">
            <div class="ai-header-left">
                <button @click="showSidebar = !showSidebar" class="ai-header-btn" title="Riwayat">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                </button>
                <div class="ai-header-info">
                    <h6 class="ai-header-name">TKS AI Assistant</h6>
                    <span class="ai-header-status" x-text="isTyping ? '⚡ Mengetik...' : '🟢 Online'"></span>
                </div>
            </div>
            <div class="ai-header-right">
                <button @click="startNewChat" class="ai-header-btn" title="Chat Baru">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                </button>
                <button @click="isOpen = false" class="ai-header-btn" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="ai-messages" x-ref="messagesContainer" @scroll.debounce="onScroll">
            {{-- Welcome --}}
            <div x-show="messages.length === 0 && !isTyping" class="ai-welcome">
                <div class="ai-welcome-logo">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        <path d="M9 9h.01" stroke-width="2" stroke-linecap="round"/>
                        <path d="M15 9h.01" stroke-width="2" stroke-linecap="round"/>
                        <path d="M9 13h6" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h5 class="ai-welcome-title">Halo! Ada yang bisa saya bantu? 👋</h5>
                <p class="ai-welcome-desc">Tanya apa saja seputar stok spare part, serial number, aging, inbound, atau outbound.</p>
                <div class="ai-chips">
                    <button @click="sendSuggestion('Cek stok spare part')" class="ai-chip">📦 Stok</button>
                    <button @click="sendSuggestion('Produk aging lebih dari 90 hari')" class="ai-chip">⚠️ Aging</button>
                    <button @click="sendSuggestion('Cari serial number')" class="ai-chip">🔍 Serial Number</button>
                    <button @click="sendSuggestion('Cek status lokasi penyimpanan')" class="ai-chip">📍 Lokasi</button>
                    <button @click="sendSuggestion('Ringkasan outbound bulan ini')" class="ai-chip">🚚 Outbound</button>
                    <button @click="sendSuggestion('Status inbound terbaru')" class="ai-chip">📋 Inbound</button>
                </div>
            </div>

            {{-- Messages loop --}}
            <template x-for="msg in messages" :key="msg.id">
                <div class="ai-msg" :class="msg.role">
                    <div class="ai-msg-body">
                        <div class="ai-msg-text" x-html="formatMessage(msg.content)"></div>
                        <div class="ai-msg-time" x-text="msg.created_at"></div>
                    </div>
                </div>
            </template>

            {{-- Typing --}}
            <div x-show="isTyping" class="ai-msg assistant">
                <div class="ai-msg-body">
                    <div class="ai-msg-text">
                        <div class="ai-typing"><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="ai-footer">
            <textarea
                x-model="inputMessage"
                @keydown.enter.prevent="!$event.shiftKey && sendMessage()"
                @keydown.escape="isOpen = false"
                placeholder="Ketik pertanyaan..."
                rows="1"
                class="ai-input"
                :disabled="isTyping"
                x-ref="messageInput"
                @input="autoResize($el)"
            ></textarea>
            <button @click="sendMessage" class="ai-send" :disabled="isTyping || !inputMessage.trim()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
/* ============================================
   TKS WMS AI Chat Widget — Clean & Modern
   ============================================ */
.ai-chat-root {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: var(--bs-body-font-family, 'Public Sans', system-ui, sans-serif);
}
[x-cloak] { display: none !important; }

/* ---- FAB ---- */
.ai-fab {
    width: 56px; height: 56px;
    border-radius: 50%; border: none;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff; cursor: pointer;
    box-shadow: 0 4px 20px rgba(99,102,241,.4);
    display: flex; align-items: center; justify-content: center;
    transition: all .25s ease;
}
.ai-fab:hover { transform: scale(1.07); box-shadow: 0 6px 28px rgba(99,102,241,.5); }

/* ---- Panel ---- */
.ai-chat-panel {
    position: absolute;
    bottom: 72px; right: 0;
    width: 460px; height: 640px;
    max-height: calc(100vh - 120px);
    background: #fff; border-radius: 20px;
    box-shadow: 0 12px 50px rgba(0,0,0,.18);
    display: flex; flex-direction: column; overflow: hidden;
}
@media (max-width: 500px) {
    .ai-chat-panel {
        width: calc(100vw - 12px); right: -4px;
        height: 540px; border-radius: 18px;
    }
}

/* Transitions */
.ai-chat-enter { animation: aiUp .25s ease-out; }
.ai-chat-leave { animation: aiDown .2s ease-in forwards; }
@keyframes aiUp   { from { opacity:0; transform:translateY(16px) scale(.96); } to { opacity:1; transform:translateY(0) scale(1); } }
@keyframes aiDown { from { opacity:1; transform:translateY(0) scale(1);   } to { opacity:0; transform:translateY(16px) scale(.96); } }

/* ---- Header ---- */
.ai-header {
    padding: 12px 16px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #fff; display: flex; align-items: center;
    justify-content: space-between; flex-shrink: 0;
}
.ai-header-left  { display:flex; align-items:center; gap:10px; }
.ai-header-info  { line-height:1.2; }
.ai-header-name  { font-size:14px; font-weight:600; margin:0; letter-spacing:-.01em; }
.ai-header-status { font-size:11px; opacity:.85; }
.ai-header-right { display:flex; gap:4px; }
.ai-header-btn {
    width:34px; height:34px; border-radius:50%; border:none;
    background:rgba(255,255,255,.15); color:#fff; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; transition: background .15s;
}
.ai-header-btn:hover { background:rgba(255,255,255,.25); }

/* ---- History Drawer ---- */
.ai-history-overlay {
    position:absolute; inset:0; background:rgba(0,0,0,.3); z-index:10;
}
.ai-history-drawer {
    position:absolute; left:0; top:0; bottom:0; width:280px;
    background:#fff; z-index:11;
    display:flex; flex-direction:column;
    transform:translateX(-100%); transition:transform .25s cubic-bezier(.4,0,.2,1);
    box-shadow:4px 0 30px rgba(0,0,0,.1);
}
.ai-history-drawer.open { transform:translateX(0); }
.ai-history-header {
    padding:16px; display:flex; align-items:center;
    justify-content:space-between; border-bottom:1px solid #f1f5f9;
}
.ai-history-header h6 { font-size:15px; font-weight:600; }
.ai-history-body { flex:1; overflow-y:auto; padding:12px; }
.ai-new-chat-btn {
    width:100%; padding:10px; border-radius:12px; border:2px dashed #e2e8f0;
    background:transparent; color:#6366f1; font-size:13px; font-weight:500;
    cursor:pointer; margin-bottom:12px; transition:all .15s;
}
.ai-new-chat-btn:hover { background:#f8fafc; border-color:#6366f1; }
.ai-history-item {
    padding:12px; border-radius:12px; cursor:pointer; margin-bottom:4px;
    transition:background .12s;
}
.ai-history-item:hover { background:#f1f5f9; }
.ai-history-item.active { background:#eef2ff; border:1px solid #e0e7ff; }
.ai-history-item-title {
    font-size:13px; font-weight:500; color:#1e293b;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.ai-history-item-meta { display:flex; justify-content:space-between; align-items:center; margin-top:4px; }
.ai-history-item-meta small { font-size:11px; color:#94a3b8; }
.ai-history-delete { background:none; border:none; color:#94a3b8; cursor:pointer; font-size:15px; padding:2px; border-radius:6px; opacity:0; transition:all .12s; }
.ai-history-item:hover .ai-history-delete { opacity:1; }
.ai-history-delete:hover { color:#ef4444; background:#fef2f2; }

/* ---- Messages ---- */
.ai-messages {
    flex:1; overflow-y:auto; padding:20px 16px;
    background:#f8fafc; scroll-behavior:smooth;
}

/* Welcome */
.ai-welcome { text-align:center; padding:40px 8px; }
.ai-welcome-logo {
    width:72px; height:72px; border-radius:50%;
    background:linear-gradient(135deg, #eef2ff, #f3e8ff);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 20px; color:#6366f1;
}
.ai-welcome-title { font-size:17px; font-weight:700; color:#1e293b; margin-bottom:4px; }
.ai-welcome-desc  { font-size:13px; color:#64748b; margin-bottom:24px; }
.ai-chips { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; }
.ai-chip {
    padding:8px 16px; border-radius:100px; border:1px solid #e2e8f0;
    background:#fff; font-size:12px; color:#475569; cursor:pointer;
    transition:all .15s; white-space:nowrap;
}
.ai-chip:hover { background:#6366f1; color:#fff; border-color:#6366f1; }

/* Message rows */
.ai-msg { display:flex; margin-bottom:20px; animation:msgIn .3s ease-out; }
@keyframes msgIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
.ai-msg.user      { justify-content:flex-end; }
.ai-msg.assistant { justify-content:flex-start; }

.ai-msg-body { max-width:88%; display:flex; flex-direction:column; }
.ai-msg.user .ai-msg-body { align-items:flex-end; }
.ai-msg.assistant .ai-msg-body { align-items:flex-start; }

.ai-msg-text {
    padding:12px 16px; border-radius:18px;
    font-size:13.5px; line-height:1.6; word-wrap:break-word;
}
.ai-msg.user .ai-msg-text {
    background:linear-gradient(135deg, #6366f1, #8b5cf6);
    color:#fff; border-bottom-right-radius:6px;
}
.ai-msg.assistant .ai-msg-text {
    background:#fff; color:#1e293b;
    border-bottom-left-radius:6px;
    box-shadow:0 1px 2px rgba(0,0,0,.04);
}
.ai-msg-text strong { font-weight:600; }
.ai-msg-text code {
    background:rgba(0,0,0,.06); padding:2px 6px; border-radius:5px;
    font-size:12px; font-family:'SF Mono','Fira Code',monospace;
}
.ai-msg.user .ai-msg-text code { background:rgba(255,255,255,.2); }
.ai-msg-time {
    font-size:10px; color:#94a3b8; margin-top:4px; padding:0 4px;
}

/* Typing */
.ai-typing { display:flex; gap:5px; padding:4px 0; }
.ai-typing span {
    width:8px; height:8px; border-radius:50%; background:#cbd5e1;
    animation:dot 1.4s infinite ease-in-out;
}
.ai-typing span:nth-child(2) { animation-delay:.15s; }
.ai-typing span:nth-child(3) { animation-delay:.3s; }
@keyframes dot {
    0%,60%,100% { transform:translateY(0); opacity:.35; }
    30% { transform:translateY(-5px); opacity:1; }
}

/* ---- Footer ---- */
.ai-footer {
    padding:12px 16px; border-top:1px solid #f1f5f9;
    background:#fff; display:flex; align-items:flex-end;
    gap:10px; flex-shrink:0;
}
.ai-input {
    flex:1; border:none; background:#f1f5f9; border-radius:16px;
    padding:10px 16px; font-size:13px; resize:none;
    max-height:100px; line-height:1.5; color:#1e293b; outline:none;
    transition:box-shadow .2s;
}
.ai-input:focus { box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.ai-input::placeholder { color:#94a3b8; }
.ai-input:disabled { opacity:.5; }
.ai-send {
    width:40px; height:40px; border-radius:50%; border:none;
    background:linear-gradient(135deg, #6366f1, #8b5cf6);
    color:#fff; cursor:pointer; display:flex; align-items:center;
    justify-content:center; flex-shrink:0; transition:all .2s;
}
.ai-send:disabled { opacity:.35; cursor:not-allowed; }
.ai-send:not(:disabled):hover { transform:scale(1.08); }

/* ---- Dark Mode ---- */
[data-bs-theme="dark"] .ai-chat-panel { background:#1a1a2e; }
[data-bs-theme="dark"] .ai-history-drawer { background:#1a1a2e; }
[data-bs-theme="dark"] .ai-history-header { border-color:#2d2d44; }
[data-bs-theme="dark"] .ai-history-header h6 { color:#e2e8f0; }
[data-bs-theme="dark"] .ai-history-item-title { color:#e2e8f0; }
[data-bs-theme="dark"] .ai-history-item:hover { background:#252540; }
[data-bs-theme="dark"] .ai-history-item.active { background:#2d2d4a; border-color:#3d3d5a; }
[data-bs-theme="dark"] .ai-new-chat-btn { border-color:#3d3d5a; color:#8b8bff; }
[data-bs-theme="dark"] .ai-new-chat-btn:hover { background:#252540; }
[data-bs-theme="dark"] .ai-messages { background:#16162a; }
[data-bs-theme="dark"] .ai-welcome-title { color:#e2e8f0; }
[data-bs-theme="dark"] .ai-chip { background:#252540; border-color:#3d3d5a; color:#cbd5e1; }
[data-bs-theme="dark"] .ai-msg.assistant .ai-msg-text { background:#252540; color:#e2e8f0; }
[data-bs-theme="dark"] .ai-footer { background:#1a1a2e; border-color:#2d2d44; }
[data-bs-theme="dark"] .ai-input { background:#252540; color:#e2e8f0; }
[data-bs-theme="dark"] .ai-history-overlay { background:rgba(0,0,0,.5); }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiChatWidget', () => ({
        isOpen: false,
        loaded: true,
        showSidebar: false,
        isTyping: false,
        currentConversationId: null,
        inputMessage: '',
        messages: [],
        conversations: [],

        init() { this.loadConversations(); },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.$refs.messageInput?.focus();
                    this.scrollToBottom();
                });
            }
        },

        async loadConversations() {
            try {
                const res = await fetch('/ai-chat/conversations');
                const data = await res.json();
                if (data.success) this.conversations = data.conversations;
            } catch (e) { console.error(e); }
        },

        async loadConversation(id) {
            if (this.currentConversationId === id) return;
            this.currentConversationId = id;
            this.messages = [];
            try {
                const res = await fetch(`/ai-chat/messages/${id}`);
                const data = await res.json();
                if (data.success) {
                    this.messages = data.messages;
                    this.$nextTick(() => this.scrollToBottom());
                }
            } catch (e) { console.error(e); }
        },

        startNewChat() {
            this.currentConversationId = null;
            this.messages = [];
            this.$nextTick(() => this.$refs.messageInput?.focus());
        },

        async sendMessage() {
            const msg = this.inputMessage.trim();
            if (!msg || this.isTyping) return;
            const tempId = 't-' + Date.now();
            const time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
            this.messages.push({ id:tempId, role:'user', content:msg, created_at:time });
            this.inputMessage = '';
            this.$nextTick(() => this.scrollToBottom());
            this.isTyping = true;
            try {
                const res = await fetch('/ai-chat/send', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'',
                    },
                    body:JSON.stringify({ message:msg, chat_conversation_id:this.currentConversationId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.messages = this.messages.filter(m=>m.id!==tempId);
                    this.messages.push({ id:'u-'+Date.now(), role:'user', content:msg, created_at:time });
                    this.messages.push({ id:'a-'+Date.now(), role:'assistant', content:data.reply, created_at:new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}) });
                    this.currentConversationId = data.chat_conversation_id;
                    await this.loadConversations();
                }
            } catch (e) {
                this.messages.push({ id:'e-'+Date.now(), role:'assistant', content:'❌ Gagal terhubung. Coba lagi.', created_at:time });
            } finally {
                this.isTyping = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        sendSuggestion(text) { this.inputMessage = text; this.sendMessage(); },

        async deleteConversation(id) {
            if (!confirm('Hapus percakapan ini?')) return;
            try {
                await fetch(`/ai-chat/conversation/${id}`, {
                    method:'DELETE',
                    headers:{ 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'' },
                });
                if (this.currentConversationId === id) this.startNewChat();
                await this.loadConversations();
            } catch (e) { console.error(e); }
        },

        autoResize(el) { el.style.height='auto'; el.style.height=Math.min(el.scrollHeight,100)+'px'; },

        scrollToBottom() {
            const c = this.$refs.messagesContainer;
            if (c) c.scrollTop = c.scrollHeight;
        },

        formatMessage(t) {
            if (!t) return '';
            return t.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
                    .replace(/\*(.+?)\*/g,'<em>$1</em>')
                    .replace(/`(.+?)`/g,'<code>$1</code>')
                    .replace(/\n/g,'<br>');
        },
    }));
});
</script>
