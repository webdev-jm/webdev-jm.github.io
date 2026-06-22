<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Models\Ticket;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string')]
    public string $description = '';

    #[Validate('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Validate('required|in:bug,feature_request,support,general')]
    public string $category = 'general';

    public function priorityOptions(): array
    {
        return array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], TicketPriority::cases());
    }

    public function categoryOptions(): array
    {
        return array_map(fn ($e) => ['value' => $e->value, 'label' => $e->label()], TicketCategory::cases());
    }

    public function save(): void
    {
        $this->validate();

        $ticket = Ticket::create([
            'user_id'     => auth()->id(),
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => $this->priority,
            'category'    => $this->category,
        ]);

        activity('created')
            ->performedOn($ticket)
            ->log(':causer.name submitted ticket ['.$ticket->ticket_number.']');

        session()->flash('message_success', __('adminlte::tickets.ticket_submitted'));
        $this->redirectRoute('tickets.show', encrypt($ticket->id), navigate: true);
    }
};
?>

<div>
    @if(session('message_success'))
        <div class="alert alert-success">{{ session('message_success') }}</div>
    @endif

    <div class="card">
        <div class="card-header py-2">
            <strong class="text-lg">{{ __('adminlte::tickets.new_ticket') }}</strong>
        </div>
        <form wire:submit="save">
            <div class="card-body">

                <livewire:forms.input-text
                    wire:model="title"
                    id="title"
                    name="title"
                    :label="__('adminlte::tickets.title')"
                    :placeholder="__('adminlte::tickets.title_placeholder')"
                    :required="true"
                />

                <div class="row">
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
                </div>

                <livewire:forms.textarea
                    wire:model="description"
                    id="description"
                    name="description"
                    :label="__('adminlte::tickets.description')"
                    :placeholder="__('adminlte::tickets.description_placeholder')"
                    :rows="6"
                    :required="true"
                />

            </div>
            <div class="card-footer text-right">
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm mr-2">{{ __('adminlte::utilities.cancel') }}</a>
                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                    </span>
                    {{ __('adminlte::tickets.submit_ticket') }}
                </button>
            </div>
        </form>
    </div>
</div>
