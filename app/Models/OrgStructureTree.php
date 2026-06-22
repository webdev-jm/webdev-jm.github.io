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

    protected $fillable = [
        'org_structure_id',
        'user_id',
        'reports_to_id',
        'title',
    ];

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }

    public function org_structure() {
        return $this->belongsTo(OrgStructure::class, 'org_structure_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
