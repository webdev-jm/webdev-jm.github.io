<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }
}
