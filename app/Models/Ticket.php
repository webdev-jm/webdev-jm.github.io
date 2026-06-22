<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'ticket_number',
        'title',
        'description',
        'status',
        'priority',
        'category',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Ticket $ticket) {
            DB::transaction(function () use ($ticket) {
                $date = now()->format('Ymd');

                $sequence = static::query()
                    ->whereDate('created_at', today())
                    ->lockForUpdate()
                    ->count() + 1;

                $ticket->ticket_number = $date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
            });
        });
    }

    public function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class)->orderBy('created_at');
    }
}
