<?php
     
namespace App\Http\Traits;

use App\Models\SystemSetting;

trait SettingTrait {

    public $systemSetting;

    public function __construct() {
        $this->systemSetting = SystemSetting::first();
    }
    
    public function getDataPerPage() {
        return $this->systemSetting->data_per_page ?? 10;
    }

    public function getEmailSending() {
        return $this->systemSetting->getEmailSending ?? 0;
    }
}