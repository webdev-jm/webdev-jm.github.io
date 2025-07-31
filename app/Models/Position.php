<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Session;

class Position extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'position'
    ];

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql');
    }
}
