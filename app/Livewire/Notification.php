<?php

namespace App\Livewire;

use Livewire\Component;

class Notification extends Component
{
    public $count = 0;
    public $notifications;

    public function render()
    {
        return view('livewire.notification');
    }

    public function mount() {
        $user = auth()->user();
        if(!empty($user)) {
            $this->notifications = $user->unreadNotifications()->orderBy('created_at', 'DESC')->limit(5)->get();
            $this->count = $user->unreadNotifications()->count();
        }
    }

    public function readNotif($notification_id) {
        $notification = auth()->user()->notifications()->where('id', $notification_id)->first();
        $notification->markAsRead();

        return redirect()->to($notification->data['action_url']);
    }
}
