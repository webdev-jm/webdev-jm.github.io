<?php

namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class Password extends Component
{
    public $user, $type;
    public $current_password, $password, $password_confirmation;
    public $password_error = '';
    public $msg = '';

    public function render()
    {
        return view('livewire.user.password');
    }

    public function mount($user, $type) {
        $this->user = $user;
        $this->type = $type;
    }
    
    public function submitForm() {
        $rules = [
            'password' => ['required', 'confirmed']
        ];

        if ($this->type === 'profile') {
            $rules['current_password'] = [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, $this->user->password)) {
                        return $fail(__('adminlte::profile.incorrect_password'));
                    }
                }
            ];
        }

        $this->validate($rules);

        $this->user->update([
            'password' => Hash::make($this->password)
        ]);

        $this->msg = __('adminlte::profile.updated_password');

        $this->reset('current_password', 'password', 'password_confirmation');
    }
}
