<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index() {
        return view('pages.system-settings.index');
    }
}
