<?php

namespace App\Models;

use App\Events\WalletBalanceUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    public const string STATUS_CHECKOUT = 'checkout';
    public const string STATUS_PENDING_PAYMENT = 'pending_payment';
    public const string STATUS_EXPIRED = 'expired';
    public const string STATUS_USER_CANCELED = 'user_canceled';
    public const string STATUS_PAYMENT_SUCCESS = 'payment_success';
    public const string STATUS_PAYMENT_FAILED = 'payment_failed';
    public const string STATUS_REVIEW = 'review';
    public const string STATUS_PROCESSING = 'processing';
    public const string STATUS_COMPLETED = 'completed';
    public const string STATUS_CANCELED = 'cancelled';

    public static array $states = [
        self::STATUS_CHECKOUT,
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_EXPIRED,
        self::STATUS_USER_CANCELED,
        self::STATUS_PAYMENT_SUCCESS,
        self::STATUS_PAYMENT_FAILED,
        self::STATUS_REVIEW,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELED,
    ];

    public static array $stateTransitions = [
        self::STATUS_CHECKOUT => [self::STATUS_PENDING_PAYMENT, self::STATUS_EXPIRED, self::STATUS_USER_CANCELED],
        self::STATUS_PENDING_PAYMENT => [self::STATUS_USER_CANCELED, self::STATUS_EXPIRED, self::STATUS_PAYMENT_SUCCESS, self::STATUS_PAYMENT_FAILED],
        self::STATUS_EXPIRED => [],
        self::STATUS_USER_CANCELED => [],
        self::STATUS_PAYMENT_SUCCESS => [self::STATUS_REVIEW],
        self::STATUS_PAYMENT_FAILED => [],
        self::STATUS_REVIEW => [self::STATUS_PROCESSING, self::STATUS_CANCELED],
        self::STATUS_PROCESSING => [self::STATUS_CANCELED, self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELED => [],
    ];

    protected $fillable = [
        'user_id',
        'user_address_id',
        'shipping_method_id',
        'total_price',
        'status',
        'short_address',
        'short_shipping_data',
        'payment_gateway',
        'use_wallet',
        'wallet_price_used'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return int
     */
    public function getCartPrice(): int
    {
        $items = $this->items()->where('payable_type', Product::class)->get();
        $cartPrice = 0;
        foreach ($items as $item) {
            $cartPrice += (int)$item->price;
        }

        return $cartPrice;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transitionTo($state): static
    {
        if (!$this->canTransitionTo($state)) {
            throw new \Exception("Invalid state transition from {$this->status} to {$state}");
        }

        $this->status = $state;
        return $this;
    }

    public function canTransitionTo($state): bool
    {
        return in_array($state, self::$stateTransitions[$this->status]);
    }

    public function getBackInventories(): void
    {
        try {
            DB::beginTransaction();

            /** @var OrderItem[] $orderItems */
            $orderItems = $this->items;
            foreach ($orderItems as $item) {
                if ($item->payable_type == Product::class) {
                    $item->payable->inventory += $item->quantity;
                    $item->payable->item_sold -= $item->quantity;
                    $item->payable->save();
                }
            }

            if ($this->use_wallet) {
                $this->user->wallet_balance += $this->wallet_price_used;
                $this->user->save();

                event((new WalletBalanceUpdated($this->user))->setOrder($this));
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            logger($exception->getMessage());
        }
    }
}
