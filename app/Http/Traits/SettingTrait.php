<?php
     
namespace App\Http\Traits;

use App\Models\SystemSetting;

trait SettingTrait {

    public $systemSetting = null;

    public function __construct() {
        $this->systemSetting = SystemSetting::first();
    }

    private function loadSystemSetting() {
        if($this->systemSetting === null) {
            $this->systemSetting = SystemSetting::first();
        }
    }
    
    public function getDataPerPage() {
        $this->loadSystemSetting();
        return $this->systemSetting->data_per_page ?? 10;
    }

    public function getEmailSending() {
        $this->loadSystemSetting();
        return $this->systemSetting->email_sending ?? 0;
    }
}