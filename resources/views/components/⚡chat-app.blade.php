<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageRead;

new class extends Component
{
    public ?int $selectedUserId = null;
    public string $search       = '';

    #[Validate('required|string|max:1000')]
    public string $newMessage = '';

    // =========================================================
    // Computed Properties
    // =========================================================

    #[Computed]
    public function myId(): int
    {
        return auth()->id();
    }

    #[Computed]
    public function authUser()
    {
        return auth()->user();
    }

    #[Computed]
    public function lastMessages(): \Illuminate\Support\Collection
    {
        $myId = $this->myId;
        return Message::where(fn($q) => $q->where('sender_id', $myId)->orWhere('receiver_id', $myId))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($msg) => $msg->sender_id === $myId ? $msg->receiver_id : $msg->sender_id)
            ->map(fn($msgs) => $msgs->first());
    }

    #[Computed]
    public function contacts(): \Illuminate\Support\Collection
    {
        $lastMessages = $this->lastMessages;

        return User::where('id', '!=', $this->myId)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->get()
            ->sortByDesc(fn($user) => $lastMessages[$user->id]?->created_at?->timestamp ?? 0)
            ->values();
    }

    #[Computed]
    public function selectedUser()
    {
        return $this->selectedUserId ? User::find($this->selectedUserId) : null;
    }

    #[Computed]
    public function chatMessages(): \Illuminate\Support\Collection
    {
        if (!$this->selectedUserId) return collect();

        return Message::where(function ($q) {
                $q->where('sender_id', $this->myId)
                  ->where('receiver_id', $this->selectedUserId);
            })
            ->orWhere(function ($q) {
                $q->where('sender_id', $this->selectedUserId)
                  ->where('receiver_id', $this->myId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(fn($msg) => $msg->created_at->toDateString());
    }

    #[Computed]
    public function unreadCounts(): \Illuminate\Support\Collection
    {
        return Message::where('receiver_id', $this->myId)
            ->whereNull('read_at')
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');
    }

    #[Computed]
    public function totalUnread(): int
    {
        return Message::where('receiver_id', $this->myId)
            ->whereNull('read_at')
            ->count();
    }

    // =========================================================
    // Actions
    // =========================================================

    public function resetChat(): void
    {
        $this->selectedUserId = null;
        $this->newMessage     = '';
        $this->search         = '';
        $this->resetValidation();
    }

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->newMessage     = '';
        $this->resetValidation();
        $this->markAsRead();
        $this->dispatch('chat:scroll-to-bottom');
        $this->dispatch('chat:focus-input');
    }

    public function sendMessage(): void
    {
        $this->validate();

        if (!$this->selectedUserId) return;

        $msg = Message::create([
            'sender_id'   => $this->myId,
            'receiver_id' => $this->selectedUserId,
            'message'     => trim($this->newMessage),
        ]);

        broadcast(new MessageSent($msg))->toOthers();

        $this->newMessage = '';
        $this->dispatch('chat:scroll-to-bottom');
        $this->dispatch('chat:focus-input');
    }

    public function markAsRead(): void
    {
        if (!$this->selectedUserId) return;

        $updated = Message::where('sender_id', $this->selectedUserId)
            ->where('receiver_id', $this->myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            broadcast(new MessageRead(
                readerId: $this->myId,
                senderId: $this->selectedUserId,
            ))->toOthers();
        }
    }

    // =========================================================
    // Real-time Listeners
    // =========================================================

    #[On('echo-private:App.Models.User.{myId},MessageSent')]
    public function receiveMessage(array $event): void
    {
        $senderId = data_get($event, 'message.sender_id');

        if ($this->selectedUserId && $this->selectedUserId == $senderId) {
            $this->markAsRead();
            $this->dispatch('chat:scroll-to-bottom');
        } else {
            $senderName = data_get($event, 'message.sender.name')
                ?? User::find($senderId)?->name
                ?? 'Someone';

            $this->dispatch('toast-message', [
                'type'    => 'info',
                'message' => "New message from {$senderName}",
            ]);
        }
    }

    #[On('echo-private:App.Models.User.{myId},MessageRead')]
    public function onMessageRead(array $event): void
    {
        $readerId = data_get($event, 'readerId');

        if ($this->selectedUserId && $this->selectedUserId == $readerId) {
            $this->dispatch('chat:scroll-to-bottom');
        }
    }
};
?>

{{-- Trigger --}}
<li class="nav-item">
    <a href="#"
       class="nav-link"
       data-toggle="modal"
       data-target="#chats-modal"
       title="Open Chat">
        <i class="fas fa-comments"></i>
        @if($this->totalUnread > 0)
            <span class="badge badge-danger navbar-badge" style="font-size: 0.65rem;">
                {{ $this->totalUnread > 99 ? '99+' : $this->totalUnread }}
            </span>
        @endif
    </a>

    @teleport('body')
    <div class="modal fade"
         id="chats-modal"
         tabindex="-1"
         aria-labelledby="chats-modal-label"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                {{-- Header --}}
                <div class="modal-header pb-2 pt-3 pt-md-4 px-4">
                    <h5 class="modal-title d-flex align-items-center"
                        id="chats-modal-label"
                        style="color: var(--accent-1); font-weight: 700;">
                        <i class="fas fa-comment-dots mr-2"></i>
                        Chats
                        @if($this->totalUnread > 0)
                            <span class="badge badge-danger badge-pill ml-2 px-2 py-1">
                                {{ $this->totalUnread > 99 ? '99+' : $this->totalUnread }} New
                            </span>
                        @endif
                    </h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            style="color: var(--text-muted);">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body pt-0">
                    <div class="row no-gutters" style="height: 65vh; min-height: 500px;">

                        {{-- ==================== Contacts Sidebar ==================== --}}
                        <div class="col-12 col-md-4 chat-sidebar d-flex flex-column h-100 pb-2 {{ $selectedUserId ? 'd-none d-md-flex' : '' }}">
                            <div class="card flex-grow-1 mb-0 d-flex flex-column">

                                {{-- Search --}}
                                <div class="p-3" style="border-bottom: 1px solid var(--glass-border-sub);">
                                    <div class="position-relative">
                                        <span class="position-absolute"
                                              style="left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); z-index: 1;">
                                            <i class="fas fa-search fa-sm"></i>
                                        </span>
                                        <input type="text"
                                               wire:model.live.debounce.300ms="search"
                                               class="form-control rounded-pill"
                                               placeholder="Search contacts..."
                                               style="padding-left: 2.5rem;">
                                    </div>
                                </div>

                                {{-- Contact List --}}
                                <div class="p-0" style="overflow-y: auto; flex: 1;">
                                    <div wire:loading.delay wire:target="search" class="text-center py-4">
                                        <i class="fas fa-spinner fa-spin" style="color: var(--accent-1);"></i>
                                    </div>
                                    <div wire:loading.remove wire:target="search" class="list-group list-group-flush">
                                        @forelse($this->contacts as $user)
                                            @php
                                                $lastMsg     = $this->lastMessages[$user->id] ?? null;
                                                $isSelected  = $selectedUserId === $user->id;
                                                $unreadCount = $this->unreadCounts[$user->id] ?? 0;
                                                $msgText     = $lastMsg?->message ?? '';
                                                $preview     = $lastMsg
                                                    ? ($lastMsg->sender_id === $this->myId ? 'You: ' : '')
                                                        . (mb_strlen($msgText) > 36 ? mb_substr($msgText, 0, 33) . '...' : $msgText)
                                                    : 'No messages yet';
                                                $msgTime = match(true) {
                                                    $lastMsg === null                            => '',
                                                    $lastMsg->created_at->isToday()             => $lastMsg->created_at->format('g:i A'),
                                                    $lastMsg->created_at->isYesterday()         => 'Yesterday',
                                                    $lastMsg->created_at->gt(now()->subDays(6)) => $lastMsg->created_at->format('D'),
                                                    default                                     => $lastMsg->created_at->format('M j'),
                                                };
                                            @endphp
                                            <button wire:click="selectUser({{ $user->id }})"
                                                    wire:key="contact-{{ $user->id }}"
                                                    class="list-group-item list-group-item-action d-flex align-items-center {{ $isSelected ? 'active' : '' }}"
                                                    style="padding: 0.8rem 1rem; border-left: none; border-right: none; border-top: none; text-align: left; width: 100%;">

                                                {{-- Avatar + online dot --}}
                                                <div class="position-relative flex-shrink-0 mr-3">
                                                    <img src="{{ $user->adminlte_image() }}"
                                                         alt="{{ $user->name }}"
                                                         style="width:44px;height:44px;object-fit:cover;border-radius:50%;border:2px solid var(--glass-border);">
                                                    @if($user->isOnline())
                                                        <span class="position-absolute rounded-circle bg-success"
                                                              style="width: 11px; height: 11px; bottom: 0; right: 0; border: 2px solid white;"></span>
                                                    @endif
                                                </div>

                                                {{-- Name + preview + time --}}
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex justify-content-between align-items-baseline">
                                                        <span class="text-truncate"
                                                              style="font-weight: 600; font-size: 0.9rem; max-width: 130px; display: block;">
                                                            {{ $user->name }}
                                                        </span>
                                                        <span class="flex-shrink-0 ml-2"
                                                              style="font-size: 0.68rem; color: var(--text-muted);">
                                                            {{ $msgTime }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <span class="text-truncate"
                                                              style="font-size: 0.77rem; color: var(--text-muted); opacity: {{ $isSelected ? '0.8' : '1' }};">
                                                            {{ $preview }}
                                                        </span>
                                                        @if($unreadCount > 0)
                                                            <span class="badge badge-primary badge-pill flex-shrink-0 ml-2"
                                                                  style="font-size: 0.68rem; min-width: 20px;">
                                                                {{ $unreadCount }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="text-center py-5 px-3">
                                                <div class="glass d-inline-block p-4 mb-3" style="border-radius: 50%;">
                                                    <i class="fas fa-user-slash fa-2x" style="color: var(--text-muted);"></i>
                                                </div>
                                                <p class="small mb-0" style="color: var(--text-muted);">No contacts found.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ==================== Chat Box ==================== --}}
                        <div class="col-12 col-md-8 d-flex flex-column h-100 {{ !$selectedUserId ? 'd-none d-md-flex' : '' }}">
                            @if($this->selectedUser)
                                <div class="card direct-chat flex-grow-1 mb-0 d-flex flex-column h-100">

                                    {{-- Chat Header --}}
                                    <div class="card-header py-2 px-3 d-flex align-items-center">
                                        {{-- Back (mobile) --}}
                                        <button wire:click="resetChat"
                                                class="btn btn-sm btn-secondary d-md-none mr-2"
                                                style="width: 34px; height: 34px; padding: 0; border-radius: 50%; display: inline-flex !important; align-items: center; justify-content: center;">
                                            <i class="fas fa-chevron-left" style="font-size: 0.8rem;"></i>
                                        </button>

                                        {{-- Avatar + online dot --}}
                                        <div class="position-relative flex-shrink-0 mr-3">
                                            <img src="{{ $this->selectedUser->adminlte_image() }}"
                                                 alt="{{ $this->selectedUser->name }}"
                                                 style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid var(--glass-border);">
                                            @if($this->selectedUser->isOnline())
                                                <span class="position-absolute rounded-circle bg-success"
                                                      style="width: 10px; height: 10px; bottom: 0; right: 0; border: 2px solid white;"></span>
                                            @endif
                                        </div>

                                        <div class="flex-grow-1">
                                            <div style="font-weight: 700; font-size: 0.95rem; line-height: 1.2; color: var(--text-primary);">
                                                {{ $this->selectedUser->name }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: {{ $this->selectedUser->isOnline() ? '#10b981' : 'var(--text-muted)' }}; display: flex; align-items: center; gap: 4px; margin-top: 1px;">
                                                <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block; flex-shrink: 0;"></span>
                                                {{ $this->selectedUser->isOnline() ? 'Online' : 'Offline' }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Messages --}}
                                    <div class="card-body p-0" style="flex: 1; overflow: hidden; position: relative;">
                                        <div id="chat-messages"
                                             class="direct-chat-messages"
                                             style="height: 100%; max-height: none; overflow-y: auto; scroll-behavior: smooth;">

                                            @forelse($this->chatMessages as $date => $dateMessages)
                                                {{-- Date Separator --}}
                                                <div class="text-center my-3">
                                                    <span class="badge badge-secondary px-3 py-1 rounded-pill"
                                                          style="font-size: 0.7rem; letter-spacing: 0.04em;">
                                                        {{ \Carbon\Carbon::parse($date)->isToday()
                                                            ? 'Today'
                                                            : (\Carbon\Carbon::parse($date)->isYesterday()
                                                                ? 'Yesterday'
                                                                : \Carbon\Carbon::parse($date)->format('M d, Y')) }}
                                                    </span>
                                                </div>

                                                @php $prevSenderId = null; @endphp
                                                @foreach($dateMessages as $msg)
                                                    @php
                                                        $isMine     = $msg->sender_id === $this->myId;
                                                        $isNewGroup = $prevSenderId !== $msg->sender_id;
                                                        $prevSenderId = $msg->sender_id;
                                                    @endphp

                                                    <div class="direct-chat-msg {{ $isMine ? 'right' : '' }}"
                                                         wire:key="msg-{{ $msg->id }}"
                                                         style="margin-bottom: {{ $isNewGroup ? '4px' : '2px' }}; margin-top: {{ $isNewGroup ? '8px' : '1px' }};">

                                                        {{-- Info row: only on first of a group --}}
                                                        @if($isNewGroup)
                                                            <div class="direct-chat-infos clearfix">
                                                                @if(!$isMine)
                                                                    <span class="direct-chat-name float-left">
                                                                        {{ explode(' ', $this->selectedUser->name)[0] }}
                                                                    </span>
                                                                @endif
                                                                <span class="direct-chat-timestamp {{ $isMine ? 'float-right' : 'float-left ml-2' }}"
                                                                      title="{{ $msg->created_at->format('M d, Y h:i A') }}"
                                                                      style="cursor: default;">
                                                                    {{ $msg->created_at->format('h:i A') }}
                                                                    @if($isMine)
                                                                        @if($msg->read_at)
                                                                            <i class="fas fa-check-double ml-1"
                                                                               style="color: var(--accent-1);"
                                                                               title="Read {{ $msg->read_at->diffForHumans() }}"></i>
                                                                        @else
                                                                            <i class="fas fa-check ml-1"
                                                                               style="color: var(--text-muted);"
                                                                               title="Sent"></i>
                                                                        @endif
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif

                                                        {{-- Avatar: visible only on first of a group --}}
                                                        <img class="{{ $isNewGroup ? '' : 'invisible' }}"
                                                             src="{{ $isMine ? $this->authUser->adminlte_image() : $this->selectedUser->adminlte_image() }}"
                                                             alt="{{ $isMine ? 'Me' : $this->selectedUser->name }}"
                                                             style="width:40px;height:40px;object-fit:cover;border-radius:50%;border:2px solid var(--glass-border);">

                                                        <div class="direct-chat-text">
                                                            {{ $msg->message }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @empty
                                                <div class="h-100 d-flex flex-column align-items-center justify-content-center"
                                                     style="opacity: 0.65;">
                                                    <div class="glass p-4 mb-3" style="border-radius: 50%;">
                                                        <i class="fas fa-paper-plane fa-2x" style="color: var(--accent-1);"></i>
                                                    </div>
                                                    <p class="mb-1" style="font-weight: 600; color: var(--text-primary);">Say hello!</p>
                                                    <small style="color: var(--text-muted);">Start a conversation with {{ explode(' ', $this->selectedUser->name)[0] }}</small>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- Input --}}
                                    <div class="card-footer px-3 px-md-4 py-3">
                                        <form wire:submit="sendMessage">
                                            <div x-data="{
                                                    charCount: 0,
                                                    init() {
                                                        this.charCount = this.$refs.input.value.length;
                                                        this.$watch('$wire.newMessage', val => {
                                                            this.charCount = (val || '').length;
                                                            this.$nextTick(() => this.autoResize());
                                                        });
                                                    },
                                                    handleKeydown(e) {
                                                        if (e.key === 'Enter' && !e.shiftKey) {
                                                            e.preventDefault();
                                                            $wire.sendMessage();
                                                        }
                                                    },
                                                    autoResize() {
                                                        const el = this.$refs.input;
                                                        el.style.height = 'auto';
                                                        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
                                                    }
                                                }">
                                                <div class="chat-input-wrapper" style="align-items: flex-end;">
                                                    <textarea
                                                        x-ref="input"
                                                        wire:model="newMessage"
                                                        x-on:keydown="handleKeydown($event)"
                                                        x-on:input="autoResize(); charCount = $el.value.length"
                                                        id="chat-message-input"
                                                        class="form-control"
                                                        placeholder="Type a message..."
                                                        autocomplete="off"
                                                        maxlength="1000"
                                                        rows="1"
                                                        style="resize: none; overflow-y: hidden; min-height: 42px; max-height: 120px; line-height: 1.5; padding-top: 9px; padding-bottom: 9px;"></textarea>

                                                    <button type="submit"
                                                            class="btn btn-primary flex-shrink-0"
                                                            wire:loading.attr="disabled"
                                                            wire:target="sendMessage"
                                                            style="width: 42px; height: 42px; padding: 0; display: inline-flex !important; align-items: center; justify-content: center; margin-left: 8px; border-radius: 50% !important;">
                                                        <span wire:loading.remove wire:target="sendMessage">
                                                            <i class="fas fa-paper-plane" style="margin-left: -2px; font-size: 0.9rem;"></i>
                                                        </span>
                                                        <span wire:loading wire:target="sendMessage">
                                                            <i class="fas fa-spinner fa-spin"></i>
                                                        </span>
                                                    </button>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                                    @error('newMessage')
                                                        <small class="text-danger d-flex align-items-center">
                                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                                        </small>
                                                    @else
                                                        <small style="font-size: 0.68rem; color: var(--text-muted);">
                                                            <kbd style="font-size: 0.65rem; padding: 1px 5px; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); border-radius: 4px; color: var(--text-secondary);">Enter</kbd>
                                                            send &nbsp;·&nbsp;
                                                            <kbd style="font-size: 0.65rem; padding: 1px 5px; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); border-radius: 4px; color: var(--text-secondary);">Shift+Enter</kbd>
                                                            new line
                                                        </small>
                                                    @enderror
                                                    <small x-text="charCount + '/1000'"
                                                           x-bind:style="charCount > 900 ? 'color: #f59e0b;' : 'color: var(--text-muted);'"
                                                           class="ml-auto"
                                                           style="font-size: 0.68rem;"></small>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            @else
                                {{-- Empty State (Desktop) --}}
                                <div class="d-none d-md-flex flex-grow-1 align-items-center justify-content-center">
                                    <div class="text-center" style="opacity: 0.55;">
                                        <div class="glass d-inline-block p-5 mb-4" style="border-radius: 50%;">
                                            <i class="fas fa-comments fa-4x" style="color: var(--accent-1);"></i>
                                        </div>
                                        <h5 style="font-weight: 300; color: var(--text-primary);">Select a contact to start chatting</h5>
                                        <p class="small mb-0" style="color: var(--text-muted);">Your messages will appear here</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
                {{-- /modal-body --}}

            </div>
        </div>
    </div>
    @endteleport
</li>

@script
<script>
    const scrollToBottom = () => {
        const el = document.getElementById('chat-messages');
        if (el) el.scrollTop = el.scrollHeight;
    };

    const focusInput = () => {
        const el = document.getElementById('chat-message-input');
        if (el) el.focus();
    };

    $wire.on('chat:scroll-to-bottom', scrollToBottom);
    $wire.on('chat:focus-input', focusInput);

    Livewire.hook('morph.updated', ({ el }) => {
        if (el.id === 'chat-messages') scrollToBottom();
    });
</script>
@endscript
