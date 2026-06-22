<?php

use App\Models\Ticket;
use App\Models\TicketComment;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;

    #[Validate('required|string')]
    public string $body = '';

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
        $this->dispatch('comment-added');
        $this->dispatch('toast-message', [['type' => 'success', 'message' => __('adminlte::tickets.comment_added')]]);
    }
};
?>

<div>
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
