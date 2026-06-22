<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Notification extends Component
{
    public $notifications;

    public $count = 0;

    public $userId;

    public function render()
    {
        return view('livewire.notification');
    }

    public function mount()
    {
        $this->userId = auth()->id();
        $this->loadNotifications();
    }

    #[On('echo-private:App.Models.User.{userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated')]
    public function receiveRealTimeNotification($event)
    {
        $this->loadNotifications();

        $this->dispatch('toast-message', [
            'type' => 'success',
            'message' => 'New notification received!',
        ]);
    }

    public function loadNotifications()
    {
        $user = auth()->user();
        if ($user) {
            $this->notifications = $user->notifications()->take(5)->get();
            $this->count = $user->unreadNotifications()->count();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications(); // Reload to update the count
        }
    }
}
