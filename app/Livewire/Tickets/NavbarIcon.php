<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Poll;
use Livewire\Component;

class NavbarIcon extends Component
{
    public int $count = 0;

    /** @var Collection<int, Ticket> */
    public $recentTickets;

    public function mount(): void
    {
        $this->loadTickets();
    }

    #[Poll(60000)]
    public function loadTickets(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $isResponder = $user->can('ticket responder');

        $query = Ticket::orderBy('created_at', 'DESC');

        if ($isResponder) {
            $this->count = (clone $query)->whereIn('status', ['open', 'in_progress'])->count();
            $this->recentTickets = (clone $query)->limit(5)->get();
        } else {
            $query->where('user_id', $user->id);
            $this->count = (clone $query)->whereIn('status', ['open', 'in_progress'])->count();
            $this->recentTickets = (clone $query)->limit(5)->get();
        }
    }

    public function render()
    {
        return view('livewire.tickets.navbar-icon');
    }
}
