<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payable_id',
        'payable_type',
        'quantity',
        'price',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payable(): BelongsTo
    {
        return $this->morphTo();
    }

    public function getItemParamForTransaction(): string
    {
        return $this->payable_type . '_' . $this->payable_id . ':' . $this->price;
    }

    public static function getDescriptiveInfo(array $orderItems): array
    {
        $params = [];
        $paramIndex = 1;
        foreach ($orderItems as $orderItem) {
            $otherParam = $orderItem->getItemParamForTransaction();
            // if one item was bigger that all the ResNum
            if (mb_strlen($otherParam) > 50) {
                $otherParam = mb_substr($otherParam, 0, 50);
            }
            if (isset($params[$paramIndex]) && mb_strlen($params[$paramIndex] . $otherParam) > 50) {
                if ($paramIndex >= 4) {
                    break;
                }
                $paramIndex++;
            }
            $params[$paramIndex] = $params[$paramIndex] ?? '';
            $params[$paramIndex] .= $otherParam;
        }

        return $params;
    }
}
