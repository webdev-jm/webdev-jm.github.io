<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender() {
        return $this->belongsTo('App\Models\User', 'sender_id');
    }

    public function receiver() {
        return $this->belongsTo('App\Models\User', 'receiver_id');
    }
}
