<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\PaymentGateway\PaymentGatewayInterface;
use App\Services\PaymentGateway\SamanGateway;
use App\Services\PaymentGateway\ZarinpalGateway;
use Illuminate\Http\Request;

class PaymentService
{
    protected $paymentGateway;

    protected const string GATEWAY_SAMAN = 'saman';
    protected const string GATEWAY_ZARINPAL = 'zarinpal';

    public function getAllPaymentGateways(): array
    {
        return [
            self::GATEWAY_SAMAN,
            self::GATEWAY_ZARINPAL,
        ];
    }

    public function calcProductPrice(Product $product, int $quantity, bool $useDiscount = true): int
    {
        if ($useDiscount) {
            if ($product->discount_percent) {
                return round(($product->price * $quantity) * ((100 - $product->discount_percent) / 100));
            } else {
                $rules = json_decode($product->discount_rules);
                $discountPercent = 0;
                foreach ($rules as $q => $percent) {
                    if ($quantity >= $q) {
                        $discountPercent = $percent;
                    }
                }

                return round(($product->price * $quantity) * ((100 - $discountPercent) / 100));
            }
        } else {
            return round(($product->price * $quantity));
        }
    }

    public function calcDiscountPrice(Discount $discount, int $price): int
    {
        if ($discount->type == Discount::TYPE_PERCENTAGE) {
            return round($discount->value * $price / 100);
        } else {
            return $discount->value;
        }
    }

    public function calcOrderPrice(Order $order, $useWallet = false): array
    {
        $price = 0;
        $walletPrice = 0;
        $grandPrice = 0;

        foreach ($order->items as $item) {
            if ($item->payable_type == Product::class) {
                $grandPrice += $this->calcProductPrice($item->payable, $item->quantity, false);
            }
        }

        $discountPrice = 0;
        foreach ($order->items as $item) {
            if ($order->discount && $order->discount->is_free_shipping && $item->payable_type == ShippingMethod::class) {
                $discountPrice = $this->calcDiscountPrice($order->discount, $item->price);
            }
            $price += ($item->price - $discountPrice);
        }

        if ($order->discount && !$order->discount->is_free_shipping) {
            $discountPrice = $this->calcDiscountPrice($order->discount, $price);
            $price -= $discountPrice;
        }

        if ($useWallet) {
            if ($price > $order->user->wallet_balance) {
                $walletPrice = $order->user->wallet_balance;
                $price -= $walletPrice;
            } else {
                $walletPrice = $price;
                $price = 0;
            }
        }

        return [
            'orderPrice' => $price,
            'grandPrice' => $grandPrice,
            'discountPrice' => $discountPrice,
            'walletPrice' => $walletPrice,
        ];
    }

    public function initTransaction(Order $order): string
    {
        $gateway = $this->getPaymentGateway($order->payment_gateway, $order);
        $result = $gateway->initializePayment($order->total_price);

        Payment::query()->create([
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'payment_method' => $order->payment_gateway,
            'payment_status' => Payment::STATUS_PENDING,
            'transaction_id' => $result['refId'],
        ]);

        return $result['url'];
    }

    public function getPaymentGateway(string $gateway, Order $order): PaymentGatewayInterface
    {
        if ($this->paymentGateway) {
            return $this->paymentGateway;
        }

        switch ($gateway) {
            case self::GATEWAY_SAMAN:
                $this->paymentGateway = new SamanGateway(config('services.saman'), $order);
                break;
            case self::GATEWAY_ZARINPAL:
                $this->paymentGateway = new ZarinpalGateway(config('services.zarinpal'), $order);
                break;
            default:
                throw new \Exception('Gateway not supported');
        }

        return $this->paymentGateway;
    }

    public function verifyTransaction(Order $order, Request $request): bool
    {
        $gateway = $this->getPaymentGateway($order->payment_gateway, $order);
        return $gateway->verifyPayment($request);
    }
}
