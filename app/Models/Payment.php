<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const string STATUS_PENDING = 'pending';
    public const string STATUS_WAITING_VERIFICATION = 'waiting_for_verification';
    public const string STATUS_VERIFIED = 'verified';
    public const string STATUS_DECLINED = 'declined';
    public const string STATUS_FAILED = 'failed';
    public const string STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
