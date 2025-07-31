<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\SystemSetting;

class SystemSettings extends Component
{
    public $system_setting;
    public $data_per_page, $email_sending;

    public function render()
    {
        return view('livewire.system-settings');
    }

    public function mount() {
        $this->system_setting = SystemSetting::first();
        $this->data_per_page = $this->system_setting->data_per_page;
        $this->email_sending = $this->system_setting->email_sending;
    }

    public function saveSetting() {
        $this->validate([
            'data_per_page' => [
                'required'
            ]
        ]);

        $changes_arr['old'] = $this->system_setting->getOriginal();

        $this->system_setting->update([
            'data_per_page' => $this->data_per_page ?? 10,
            'email_sending' => $this->email_sending ?? 0
        ]);

        $changes_arr['changes'] = $this->system_setting->getChanges();

        // log
        activity('updated')
            ->performedOn($this->system_setting)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated system settings');

        return redirect()->route('system-setting.index')->with([
            'message_success' => 'System settings has been updated successfully.'
        ]);
    }
}
