<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketComment;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;

    #[Validate('required|string')]
    public string $body = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, TicketComment> */
    public function getCommentsProperty()
    {
        return $this->ticket->comments()->with('user')->get();
    }

    public function addComment(): void
    {
        $this->validate();

        abort_unless(
            auth()->id() === $this->ticket->user_id || auth()->user()->can('ticket responder'),
            403
        );

        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id'   => auth()->id(),
            'body'      => $this->body,
        ]);

        if ($this->ticket->status->value === 'open') {
            $this->ticket->update(['status' => 'in_progress']);
            $this->ticket->refresh();
        }

        $this->body = '';
        $this->dispatch('toast-message', [['type' => 'success', 'message' => __('adminlte::tickets.comment_added')]]);
    }

    public function deleteComment(string $encryptedId): void
    {
        $comment = TicketComment::findOrFail(decrypt($encryptedId));

        abort_unless(
            auth()->id() === $comment->user_id || auth()->user()->can('ticket responder'),
            403
        );

        $comment->delete();
        $this->dispatch('toast-message', [['type' => 'success', 'message' => __('adminlte::tickets.comment_deleted')]]);
    }
};
?>

<div>
    <div class="px-3 pt-3">
        <strong>{{ __('adminlte::tickets.comments', ['count' => $this->comments->count()]) }}</strong>
    </div>

    @foreach($this->comments as $comment)
        <div class="d-flex p-3 border-top">
            <img src="{{ $comment->user->adminlte_image() }}" alt="{{ $comment->user->name }}"
                 style="width:36px;height:36px;object-fit:cover;border-radius:50%;" class="mr-3 flex-shrink-0">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <strong>{{ $comment->user->name }}</strong>
                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-0 mt-1" style="white-space: pre-wrap;">{{ $comment->body }}</p>
            </div>
            @if(auth()->id() === $comment->user_id || auth()->user()->can('ticket responder'))
                <button
                    class="btn btn-xs btn-outline-danger ml-2 align-self-start"
                    wire:click="deleteComment('{{ encrypt($comment->id) }}')"
                    wire:confirm="{{ __('adminlte::tickets.delete_comment_confirm') }}"
                >
                    <i class="fa fa-trash"></i>
                </button>
            @endif
        </div>
    @endforeach

    @if(in_array($ticket->status->value, ['open', 'in_progress']))
        <div class="p-3 border-top">
            <form wire:submit="addComment">
                <livewire:forms.textarea
                    wire:model="body"
                    id="body"
                    name="body"
                    :placeholder="__('adminlte::tickets.comment_placeholder')"
                    :rows="3"
                    :required="true"
                />
                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                    <span wire:loading wire:target="addComment">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                    </span>
                    {{ __('adminlte::tickets.add_comment') }}
                </button>
            </form>
        </div>
    @endif
</div>
