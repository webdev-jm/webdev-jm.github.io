<?php

namespace App\Models;

use Database\Factories\SyncRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRequest extends Model
{
    /** @use HasFactory<SyncRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'method',
        'url',
        'payload',
        'client_timestamp',
        'status',
        'server_response',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'server_response' => 'array',
            'client_timestamp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
