<?php

namespace App\Services\PaymentGateway;

use Illuminate\Http\Request;

class PaymentGatewayService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function initializePayment(float $amount, array $options = []): array
    {
        return $this->gateway->initializePayment($amount, $options);
    }

    public function verifyPayment(Request $request): bool
    {
        return $this->gateway->verifyPayment($request);
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        return $this->gateway->refundPayment($transactionId, $amount);
    }
}
