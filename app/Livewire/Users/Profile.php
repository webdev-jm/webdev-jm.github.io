<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Helpers\FileSavingHelper;

class Profile extends Component
{
    use WithFileUploads;

    public $user;
    public $profile_pic;
    public $msg = '';

    public function render()
    {
        return view('livewire.users.profile');
    }

    public function mount($user) {
        $this->user = $user;
    }

    public function changeProfile() {
        $this->validate([
            'profile_pic' => [
                'required'
            ]
        ]);

        $path = NULL;
        if(!empty($this->profile_pic)) {
            $path = FileSavingHelper::saveFile($this->profile_pic, $this->user->id, 'profile-pic');
        }

        $this->user->update([
            'profile_pic' => $path
        ]);

        return redirect()->route('profile', encrypt($this->user->id))->with([
            'message_success' => __('adminlte::profile.profile_pic_saved')
        ]);
    }
}
