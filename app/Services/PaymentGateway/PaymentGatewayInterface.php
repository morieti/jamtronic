<?php

namespace App\Services\PaymentGateway;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function getTransactionId(Request $request): string;

    public function initializePayment(int $amount, array $options = []): array;

    public function verifyPayment(Request $request): bool;

    public function refundPayment(string $transactionId, int $amount): array;
}
