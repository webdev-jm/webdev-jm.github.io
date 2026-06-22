<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string')]
    public string $description = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Validate('required|in:bug,feature_request,support,general')]
    public string $category = 'general';

    #[Validate('nullable|exists:users,id')]
    public mixed $assigned_to = null;

    public function mount(Ticket $ticket): void
    {
        $this->ticket      = $ticket;
        $this->title       = $ticket->title;
        $this->description = $ticket->description;
        $this->priority    = $ticket->priority->value;
        $this->category    = $ticket->category->value;
        $this->assigned_to = $ticket->assigned_to;
    }

    public function priorityOptions(): array
    {
        return array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], TicketPriority::cases());
    }

    public function categoryOptions(): array
    {
        return array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], TicketCategory::cases());
    }

    public function responderOptions(): array
    {
        return User::select('id', 'name')->orderBy('name')
            ->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name])
            ->prepend(['value' => '', 'label' => __('adminlte::tickets.unassigned')])
            ->toArray();
    }

    public function save(): void
    {
        $this->validate();

        $this->ticket->update([
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => $this->priority,
            'category'    => $this->category,
            'assigned_to' => $this->assigned_to ?: null,
        ]);

        activity('updated')
            ->performedOn($this->ticket)
            ->log(':causer.name updated ticket ['.$this->ticket->ticket_number.']');

        session()->flash('message_success', __('adminlte::tickets.ticket_updated'));
        $this->redirectRoute('tickets.show', encrypt($this->ticket->id), navigate: true);
    }
};
?>

<div>
    @if(session('message_success'))
        <div class="alert alert-success">{{ session('message_success') }}</div>
    @endif

    <div class="card">
        <div class="card-header py-2">
            <strong class="text-lg">{{ __('adminlte::tickets.edit_ticket') }}</strong>
        </div>
        <form wire:submit="save">
            <div class="card-body">

                <livewire:forms.input-text
                    wire:model="title"
                    id="title"
                    name="title"
                    :label="__('adminlte::tickets.title')"
                    :required="true"
                />

                <div class="row">
                    <div class="col-md-6">
                        <livewire:forms.select-field
                            wire:model="priority"
                            id="priority"
                            name="priority"
                            :label="__('adminlte::tickets.priority')"
                            :options="$this->priorityOptions()"
                            :encrypt-model="false"
                            :required="true"
                        />
                    </div>
                    <div class="col-md-6">
                        <livewire:forms.select-field
                            wire:model="category"
                            id="category"
                            name="category"
                            :label="__('adminlte::tickets.category')"
                            :options="$this->categoryOptions()"
                            :encrypt-model="false"
                            :required="true"
                        />
                    </div>
                </div>

                <livewire:forms.select-field
                    wire:model="assigned_to"
                    id="assigned_to"
                    name="assigned_to"
                    :label="__('adminlte::tickets.assign_to')"
                    :options="$this->responderOptions()"
                    :encrypt-model="false"
                    :searchable="true"
                />

                <livewire:forms.textarea
                    wire:model="description"
                    id="description"
                    name="description"
                    :label="__('adminlte::tickets.description')"
                    :rows="6"
                    :required="true"
                />

            </div>
            <div class="card-footer text-right">
                <a href="{{ route('tickets.show', encrypt($ticket->id)) }}" class="btn btn-secondary btn-sm mr-2">{{ __('adminlte::utilities.cancel') }}</a>
                <button type="submit" class="btn btn-success btn-sm" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                    </span>
                    {{ __('adminlte::utilities.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
