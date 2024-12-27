<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;

class Ticket extends Model
{
    use HasFactory, Searchable;

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

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'tickets_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int)$this->id,
            'user_id' => (int)$this->user_id,
            'title' => $this->title,
            'ticket_subject_id' => (int)$this->ticket_subject_id,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }

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
