<?php

use Livewire\Component;
use App\Ai\Agents\SupportAssistant;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;

new class extends Component
{
    public $selectedConversationId;
    public $newMessage = '';

    public function getHistoryProperty() {
        return AgentConversation::where('user_id', auth()->id())->latest()->get();
    }

    public function getMessagesProperty() {
        if (!$this->selectedConversationId) {
            return collect();
        }

        return AgentConversationMessage::where('conversation_id', $this->selectedConversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function selectConversation($conversationId) {
        $this->selectedConversationId = $conversationId;
        $this->newMessage = '';
        $this->dispatch('conversation-selected');
    }

    public function newConversation() {
        $this->selectedConversationId = null;
        $this->newMessage = '';
    }

    public function sendMessage() {
        if (empty(trim($this->newMessage))) {
            return;
        }

        if (!empty($this->selectedConversationId)) {
            (new SupportAssistant)
                ->continue($this->selectedConversationId, auth()->user())
                ->prompt($this->newMessage);
        } else {
            (new SupportAssistant)
                ->forUser(auth()->user())
                ->prompt($this->newMessage);

            $this->selectedConversationId = AgentConversation::where('user_id', auth()->id())
                ->latest()
                ->first()?->id;
        }

        $this->reset('newMessage');
        $this->dispatch('message-sent');
    }
};
?>

<div
    x-data="{
        sidebarOpen: window.innerWidth >= 992,
        init() {
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) this.sidebarOpen = true;
            });
            this.$nextTick(() => this.scrollToBottom());
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesList;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }"
    @message-sent.window="scrollToBottom()"
    @conversation-selected.window="scrollToBottom()"
    class="position-relative"
>
    {{-- Mobile backdrop --}}
    <div
        x-show="sidebarOpen && window.innerWidth < 992"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="position-fixed w-100 h-100"
        style="top: 0; left: 0; background: var(--overlay-bg); backdrop-filter: blur(4px); z-index: 1040;"
        x-cloak
    ></div>

    {{-- Mobile top bar --}}
    <div class="d-flex align-items-center mb-2 d-lg-none" style="gap: 8px;">
        <button type="button" class="btn btn-sm btn-secondary" @click="sidebarOpen = !sidebarOpen">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
            </svg>
            History
        </button>
        <button type="button" class="btn btn-sm btn-primary" wire:click="newConversation">
            + New Chat
        </button>
    </div>

    {{-- Layout --}}
    <div class="d-flex" style="gap: 12px; height: clamp(450px, 65vh, 720px);">

        {{-- ── Sidebar ── --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            class="cs-sidebar d-flex flex-column"
        >
            {{-- Sidebar header (desktop) --}}
            <div class="d-none d-lg-flex align-items-center justify-content-between mb-3 px-1">
                <span class="small text-uppercase" style="font-weight: 600; letter-spacing: .06em; color: var(--text-muted);">History</span>
                <button type="button" class="btn btn-sm btn-primary" wire:click="newConversation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    New
                </button>
            </div>

            {{-- Mobile close --}}
            <div class="d-flex d-lg-none align-items-center justify-content-between mb-3 px-1">
                <span style="font-weight: 600; color: var(--text-primary);">Chat History</span>
                <button type="button" class="btn btn-sm btn-secondary" @click="sidebarOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Conversation list --}}
            <div class="flex-grow-1" style="overflow-y: auto;">
                @forelse($this->history as $conversation)
                    <div
                        wire:key="conv-{{ $conversation->id }}"
                        wire:click="selectConversation('{{ $conversation->id }}')"
                        @click="if(window.innerWidth < 992) sidebarOpen = false"
                        class="cs-conv-item {{ $selectedConversationId == $conversation->id ? 'active' : '' }}"
                    >
                        <div class="cs-conv-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M8 9h8"/><path d="M8 13h6"/>
                                <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                            </svg>
                        </div>
                        <div class="cs-conv-body">
                            <div class="cs-conv-title">{{ $conversation->title ?? 'Untitled Chat' }}</div>
                            <div class="cs-conv-time">{{ $conversation->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center small py-5" style="color: var(--text-muted);">No conversations yet</div>
                @endforelse
            </div>
        </div>

        {{-- ── Main chat panel ── --}}
        <div class="flex-grow-1 d-flex flex-column card overflow-hidden" style="min-width: 0;">

            {{-- Chat header --}}
            <div class="card-header d-flex align-items-center py-2 px-3" style="gap: 10px;">
                <div class="cs-ai-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                        <path d="M6 12a6 6 0 0 0 6 6"/><path d="M6 12a6 6 0 0 1 6 -6"/>
                        <path d="M18 12a6 6 0 0 1 -6 6"/><path d="M18 12a6 6 0 0 0 -6 -6"/>
                    </svg>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div style="font-weight: 700; font-size: .9rem; line-height: 1; color: var(--text-primary);">AI Support</div>
                    <div style="font-size: .75rem; line-height: 1; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-muted);">
                        @if($selectedConversationId)
                            {{ $this->history->firstWhere('id', $selectedConversationId)?->title ?? 'Support Chat' }}
                        @else
                            Start a new conversation
                        @endif
                    </div>
                </div>
                <span class="badge badge-success d-flex align-items-center" style="gap: 5px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block; animation: cs-pulse 2s infinite; flex-shrink: 0;"></span>
                    Online
                </span>
            </div>

            {{-- Messages --}}
            <div class="card-body flex-grow-1 p-3" style="overflow-y: auto;" x-ref="messagesList">
                @if($this->messages->isNotEmpty())
                    @foreach($this->messages as $message)
                        <div
                            wire:key="msg-{{ $message->id }}"
                            class="d-flex mb-3 align-items-end {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }}"
                        >
                            @if($message->role !== 'user')
                                <div class="cs-avatar cs-avatar-ai mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                        <path d="M6 12a6 6 0 0 0 6 6"/><path d="M6 12a6 6 0 0 1 6 -6"/>
                                        <path d="M18 12a6 6 0 0 1 -6 6"/><path d="M18 12a6 6 0 0 0 -6 -6"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="cs-bubble {{ $message->role === 'user' ? 'cs-bubble-user' : 'cs-bubble-ai' }}">
                                {!! nl2br(e($message->content)) !!}
                                <div class="cs-bubble-time">{{ $message->created_at->format('g:i A') }}</div>
                            </div>

                            @if($message->role === 'user')
                                <div class="cs-avatar cs-avatar-user ml-2">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Typing indicator --}}
                    <div wire:loading wire:target="sendMessage" class="d-flex justify-content-start mb-3 align-items-end">
                        <div class="cs-avatar cs-avatar-ai mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                <path d="M6 12a6 6 0 0 0 6 6"/><path d="M6 12a6 6 0 0 1 6 -6"/>
                                <path d="M18 12a6 6 0 0 1 -6 6"/><path d="M18 12a6 6 0 0 0 -6 -6"/>
                            </svg>
                        </div>
                        <div class="cs-bubble cs-bubble-ai cs-typing">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center py-5" style="opacity: 0.6;">
                        <div class="glass p-4 mb-3" style="border-radius: 50%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-1);">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M8 9h8"/><path d="M8 13h6"/>
                                <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z"/>
                            </svg>
                        </div>
                        <p class="mb-1" style="font-weight: 600; color: var(--text-primary);">How can I help you today?</p>
                        <p class="small mb-0" style="color: var(--text-muted);">Type a message below to get started.</p>
                    </div>
                @endif
            </div>

            {{-- Input footer --}}
            <div class="card-footer px-3 py-3">
                <div class="chat-input-wrapper" style="align-items: flex-end;">
                    <textarea
                        class="form-control"
                        placeholder="Type a message…"
                        wire:model="newMessage"
                        rows="1"
                        style="resize: none; min-height: 42px; max-height: 120px; overflow-y: hidden; line-height: 1.5; padding-top: 9px; padding-bottom: 9px;"
                        x-on:keydown="if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); $wire.sendMessage(); }"
                        x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                    ></textarea>
                    <button
                        type="button"
                        class="btn btn-primary flex-shrink-0"
                        style="width: 42px; height: 42px; padding: 0; display: inline-flex !important; align-items: center; justify-content: center; margin-left: 8px; border-radius: 50% !important;"
                        wire:click="sendMessage"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                    >
                        <span wire:loading.remove wire:target="sendMessage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-left: -2px;">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                                <path d="M21 3l-6.5 18a0.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a0.55 .55 0 0 1 0 -1l18 -6.5"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="sendMessage">
                            <i class="fas fa-spinner fa-spin" style="font-size: 0.85rem;"></i>
                        </span>
                    </button>
                </div>
                <div class="mt-2" style="font-size: .68rem; color: var(--text-muted);">
                    <kbd style="font-size: .65rem; padding: 1px 5px; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); border-radius: 4px; color: var(--text-secondary);">Enter</kbd>
                    send &nbsp;·&nbsp;
                    <kbd style="font-size: .65rem; padding: 1px 5px; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); border-radius: 4px; color: var(--text-secondary);">Shift+Enter</kbd>
                    new line
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }

/* ── Sidebar ── */
.cs-sidebar {
    width: 250px;
    flex-shrink: 0;
    background: var(--glass-bg);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border: 1px solid var(--glass-border);
    border-radius: var(--glass-radius);
    box-shadow: var(--glass-shadow-sm);
    padding: 1rem .75rem;
    overflow: hidden;
}

@media (max-width: 991.98px) {
    .cs-sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0; right: auto;
        width: 270px;
        z-index: 1050;
        border-radius: 0;
        border-left: none;
        box-shadow: 4px 0 24px rgba(0, 0, 0, .2);
        padding: 1.25rem .75rem;
    }
}

/* ── Conversation items ── */
.cs-conv-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .625rem;
    border-radius: var(--glass-radius-sm);
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    margin-bottom: .25rem;
    color: var(--text-secondary);
    border-left: 3px solid transparent;
}
.cs-conv-item:hover {
    background: var(--glass-bg-hover);
    color: var(--text-primary);
}
.cs-conv-item.active {
    background: var(--sidebar-active-bg);
    color: var(--accent-1);
    border-left-color: var(--accent-1);
}
.cs-conv-icon { flex-shrink: 0; opacity: .6; }
.cs-conv-item.active .cs-conv-icon { opacity: 1; color: var(--accent-1); }
.cs-conv-body { min-width: 0; }
.cs-conv-title { font-size: .83rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: inherit; }
.cs-conv-time { font-size: .72rem; color: var(--text-muted); margin-top: 1px; }

/* ── AI header avatar ── */
.cs-ai-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 10px var(--accent-glow);
}

/* ── Message avatars ── */
.cs-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .72rem; font-weight: 700;
}
.cs-avatar-ai {
    background: linear-gradient(135deg, rgba(99, 102, 241, .15), rgba(139, 92, 246, .15));
    border: 1px solid var(--glass-border);
    color: var(--accent-1);
}
.cs-avatar-user {
    background: var(--glass-bg-hover);
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
}

/* ── Bubbles ── */
.cs-bubble {
    max-width: min(72%, 480px);
    padding: .6rem .9rem;
    border-radius: 18px;
    font-size: .875rem;
    line-height: 1.55;
    word-break: break-word;
}
.cs-bubble-ai {
    background: var(--glass-bg-hover);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
    border-bottom-left-radius: .25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
}
.cs-bubble-user {
    background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    color: #fff;
    border-bottom-right-radius: .25rem;
    box-shadow: 0 4px 15px var(--accent-glow);
}
.cs-bubble-time {
    font-size: .65rem;
    opacity: .6;
    margin-top: .3rem;
    text-align: right;
}

/* ── Typing dots ── */
.cs-typing {
    display: flex; align-items: center; gap: 5px;
    padding: .65rem .9rem;
}
.cs-typing span {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--text-muted);
    animation: cs-bounce 1.2s infinite;
}
.cs-typing span:nth-child(2) { animation-delay: .2s; }
.cs-typing span:nth-child(3) { animation-delay: .4s; }

@keyframes cs-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: .35; }
    30% { transform: translateY(-5px); opacity: 1; }
}

/* ── Online pulse ── */
@keyframes cs-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .4; }
}

/* ── Spinner ── */
@keyframes icon-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.icon-spin { animation: icon-spin .8s linear infinite; }

@media (prefers-reduced-motion: reduce) {
    .cs-typing span, .icon-spin { animation: none; }
}
</style>
