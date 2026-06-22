<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class Settings extends Component
{
    public $user;
    public $name, $email, $current_password, $password, $password_confirmation;

    public $msg;

    public function render()
    {
        return view('livewire.users.settings');
    }

    public function mount($user) {
        $this->user = $user;

        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }

    public function saveSettings() {
        $this->validate([
            'name' => [
                'required'
            ],
            'email' => [
                'required',
                Rule::unique((new User)->getTable())->ignore($this->user->id)
            ]
        ]);

        $changes_arr['old'] = $this->user->getOriginal();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email
        ]);

        $changes_arr['changes'] = $this->user->getChanges();

        // logs
        activity('updated')
            ->performedOn($this->user)
            ->withProperties($changes_arr)
            ->log(':causer.name updated profile details.');

        $this->msg = 'Profile details has been updated.';
    }
}
