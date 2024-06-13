<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Ticket extends Model
{
    use HasFactory;

    public const string STATUS_OPEN = 'open';
    public const string STATUS_RESPONDED = 'responded';
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'title',
        'ticket_subject_id',
        'description',
        'file',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(TicketSubject::class, 'ticket_subject_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
