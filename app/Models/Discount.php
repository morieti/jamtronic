<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory;

    public const string TYPE_PERCENTAGE = 'percentage';
    public const string TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'usage_limit',
        'used',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
        'is_free_shipping'
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(DiscountUsage::class);
    }

    public function isUsable(int $userId, Order $order): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at > now() || $this->expires_at <= now()) {
            return false;
        }

        if ($this->used >= $this->usage_limit) {
            return false;
        }

        $userUsedCount = $this->usages()->where('user_id', $userId)->count();
        if ($userUsedCount >= $this->per_user_limit) {
            return false;
        }

        if ($this->min_order_value < $order->total_price) {
            return false;
        }

        return true;
    }

    public function useDiscount(int $userId, int $orderId): bool
    {
        return $this->usages()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'used_at' => now()
        ]);
    }

    public function returnDiscount(int $userId, int $orderId): bool
    {
        return $this->usages()
            ->where('user_id', $userId)
            ->where('order_id', $orderId)
            ->delete();
    }
}
