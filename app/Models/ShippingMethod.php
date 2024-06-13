<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'price_caption',
        'parent_id',
        'status',
        'starts_at',
        'ends_at',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'parent_id');
    }

    public function getShippingDetailString(): string
    {
        if ($this->parent_id) {
            return $this->parent->getShippingDetailString() . ' - ' . $this->title;
        } else {
            return $this->title;
        }
    }

    public static function getSelectableMethodsList(UserAddress $userAddress)
    {
        $methods = ShippingMethod::query();

        if ($userAddress->city_id != UserAddress::CITY_TEHRAN) {
            return $methods
                ->whereNotIn('type', ['express', 'express_option'])
                ->get();
        }

        $parentMethods = $methods
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        $today = now()->setTime(0, 0, 0);
        $weekLater = now()->setTime(23, 59, 59)->addWeek();
        foreach ($parentMethods as $method) {
            if ($method->type == 'express') {
                $children = ShippingMethod::query()
                    ->with('children')
                    ->where('parent_id', $method->id)
                    ->where('starts_at', '>=', $today)
                    ->where('ends_at', '<=', $weekLater)
                    ->get();

                $method->children = $children;
            }
        }
        return $parentMethods;
    }

    public static function getFlatActiveMethods(UserAddress $userAddress)
    {
        $methods = ShippingMethod::query();

        if ($userAddress->city_id != UserAddress::CITY_TEHRAN) {
            return $methods
                ->where('status', 'active')
                ->whereNotIn('type', ['express', 'express_option'])
                ->get();
        }

        $parentMethods = $methods
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        $today = now()->setTime(0, 0, 0);
        $weekLater = now()->setTime(23, 59, 59)->addWeek();
        $flattedMethods = [];
        foreach ($parentMethods as $method) {
            $flattedMethods[] = $method;
            if ($method->type == 'express') {
                $children = ShippingMethod::query()
                    ->with('children')
                    ->where('parent_id', $method->id)
                    ->where('status', 'active')
                    ->where('starts_at', '>=', $today)
                    ->where('ends_at', '<=', $weekLater)
                    ->get();

                $flattedMethods += $children;
            }
        }
        return $flattedMethods;
    }
}
