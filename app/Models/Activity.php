<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Support\Facades\Session;

class Activity extends SpatieActivity
{
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }
}
