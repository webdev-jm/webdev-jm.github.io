<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }
}
