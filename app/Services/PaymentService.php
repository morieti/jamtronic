<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\PaymentGateway\PaymentGatewayInterface;
use App\Services\PaymentGateway\SamanGateway;
use App\Services\PaymentGateway\ZarinpalGateway;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    protected $paymentGateway;

    public function calcProductPrice(Product $product, int $quantity): int
    {
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
    }

    public function calcOrderPrice(Order $order, $useWallet = false): array
    {
        $price = 0;
        $walletPrice = 0;
        foreach ($order->items as $item) {
            $price += $item->price;
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
            'walletPrice' => $walletPrice,
        ];
    }

    public function getPaymentGateway(string $gateway, Order $order): PaymentGatewayInterface
    {
        if ($this->paymentGateway) {
            return $this->paymentGateway;
        }

        switch ($gateway) {
            case 'saman':
                $this->paymentGateway = new SamanGateway(config('services.saman'), $order);
                break;
            case 'zarinpal':
                $this->paymentGateway = new ZarinpalGateway(config('services.zarinpal'), $order);
                break;
            default:
                throw new \Exception('Gateway not supported');
        }

        return $this->paymentGateway;
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

    public function verifyTransaction(Order $order, Request $request): bool
    {
        $gateway = $this->getPaymentGateway($order->payment_gateway, $order);
        return $gateway->verifyPayment($request);
    }
}
