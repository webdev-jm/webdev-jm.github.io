<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\User;

class OnlineUsers extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $users = User::whereNotNull('last_activity')
            ->where('last_activity', '>=', $hour_ago)
            ->orderBy('last_activity', 'DESC')
            ->paginate();

        return view('livewire.online-users')->with([
            'users' => $users
        ]);
    }
}
