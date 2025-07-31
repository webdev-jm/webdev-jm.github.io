<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class OrgStructureTree extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }
}
