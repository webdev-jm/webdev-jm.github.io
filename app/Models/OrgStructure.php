<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class OrgStructure extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
    ];

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }

    public function structure_trees() {
        return $this->hasMany('App\Models\OrgStructureTree', 'org_structure_id', 'id');
    }
}
