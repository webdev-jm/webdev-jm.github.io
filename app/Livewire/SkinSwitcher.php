<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class SkinSwitcher extends Component
{
    /** @var array<string, array{label: string, icon: string}> */
    public array $skins = [
        'default' => ['label' => 'Default', 'icon' => 'fas fa-circle'],
        'glass' => ['label' => 'Glass',   'icon' => 'fas fa-gem'],
        'neumorphic'  => ['label' => 'Neumorphic',  'icon' => 'fas fa-layer-group'],
        'claymorphic' => ['label' => 'Claymorphic', 'icon' => 'fas fa-cloud'],
    ];

    public function render(): View
    {
        return view('livewire.skin-switcher');
    }

    public function switchSkin(string $skin): void
    {
        if (! array_key_exists($skin, $this->skins)) {
            return;
        }

        auth()->user()->update(['skin' => $skin]);

        $this->redirect(request()->header('Referer') ?? route('home'), navigate: false);
    }
}
