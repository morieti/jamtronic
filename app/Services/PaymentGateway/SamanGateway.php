<?php

namespace App\Services\PaymentGateway;


use App\Models\Order;
use Illuminate\Http\Request;
use SoapClient;

class SamanGateway implements PaymentGatewayInterface
{
    public const string REQUEST_PAYMENT_URL = 'https://sep.shaparak.ir/Payments/InitPayment.asmx?WSDL';
    public const string START_PAYMENT_URL = 'https://sep.shaparak.ir/payment.aspx';
    public const string VERIFY_PAYMENT_URL = 'https://acquirer.samanepay.com/payments/referencepayment.asmx?WSDL';

    protected SoapClient $client;
    protected array $configs;
    protected Order $order;

    public function __construct(array $configs, Order $order)
    {
        $this->configs = $configs;
        $this->order = $order;
    }

    public function getClient($url): void
    {
        $this->client = new SoapClient($url);
    }

    public function initializePayment(int $amount, array $options = []): array
    {
        $this->getClient(self::START_PAYMENT_URL);

        $token = $this->client->RequestToken(
            $this->configs['merchant_id'],
            $this->order->id,
            $amount,
            $this->order->user->mobile,
            route('payment.callback', ['orderId' => $this->order->id])
        );

        return [
            'url' => route('payment.saman', ['token' => $token]),
            'refId' => $token
        ];
    }

    public function getTransactionId(Request $request): string
    {
        return $request->input('RefNum', '');
    }

    public function verifyPayment(Request $request): bool
    {
        $this->getClient(self::VERIFY_PAYMENT_URL);

        $refNum = $request->input('RefNum');
        $state = $request->input('State');

        if (!$refNum || $state != 'OK') {
            logger()->error("[Saman] error " . $refNum . ' stated not OK: ' . $state);
            return false;
        }

        $response = $this->client->VerifyTransaction($refNum, $this->configs['merchant_id']);
        if ($response != ($this->order->total_price * 10)) {
            logger()->error("[Saman] error " . $refNum . ' price mismatched: ' . $state);
            return false;
        }

        return true;
    }

    public function refundPayment(string $transactionId, int $amount): array
    {
        return [];
    }
}
