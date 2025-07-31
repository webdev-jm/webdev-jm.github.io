<?php

namespace App\Livewire;

use Livewire\Component;

class DarkmodeToggle extends Component
{
    public function render()
    {
        return view('livewire.darkmode-toggle');
    }

    public function changeMode() {
        if(auth()->user()->dark_mode) {
            auth()->user()->update([
                'dark_mode' => 0
            ]);
        } else {
            auth()->user()->update([
                'dark_mode' => 1
            ]);
        }
    }
}
