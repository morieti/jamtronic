<?php

namespace App\Services\PaymentGateway;


use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ZarinpalGateway implements PaymentGatewayInterface
{
    public const string REQUEST_PAYMENT_URL = 'https://api.zarinpal.com/pg/v4/payment/request.json';
    public const string START_PAYMENT_URL = 'https://www.zarinpal.com/pg/StartPay';
    public const string VERIFY_PAYMENT_URL = 'https://api.zarinpal.com/pg/v4/payment/verify.json';

    protected array $configs;
    protected Order $order;

    public function __construct(array $configs, Order $order)
    {
        $this->configs = $configs;
        $this->order = $order;
    }

    public function initializePayment(int $amount, array $options = []): array
    {
        $data = $this->prepareData($amount);
        $jsonData = json_encode($data);

        $ch = curl_init(self::REQUEST_PAYMENT_URL);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);

        if ($err) {
            logger()->error('[Zarinpal] cURL Error #:' . $err);
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    $url = sprintf('Location: %s/%s', self::START_PAYMENT_URL, $result['data']['authority']);
                    return [
                        'url' => $url,
                        'refId' => $result['data']['authority']
                    ];
                }
            } else {
                logger()->error('[Zarinpal] Error Code: ' . $result['errors']['code'] . ' message: ' . $result['errors']['message']);
            }
        }

        return [
            'status' => false,
            'error' => $result,
        ];
    }

    protected function prepareData(int $amount): array
    {
        /** @var OrderItem[] $orderItems */
        $orderItems = $this->order->items;
        $params = OrderItem::getDescriptiveInfo($orderItems);

        return [
            'merchant_id' => $this->configs['merchant_id'],
            'amount' => $amount,
            'callback_url' => route('payment.callback', ['orderId' => $this->order->id]),
            'description' => implode('\n', $params),
            'metadata' => [
                'mobile' => $this->order->user->mobile
            ],
        ];
    }

    public function getTransactionId(Request $request): string
    {
        return $request->input('Authority', '');
    }

    public function verifyPayment(Request $request): bool
    {
        $data = [
            'merchant_id' => $this->configs['merchant_id'],
            'authority' => $request->input('Authority'),
            'amount' => $this->order->total_price
        ];
        $jsonData = json_encode($data);

        $ch = curl_init(self::VERIFY_PAYMENT_URL);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);
        curl_close($ch);

        if ($err) {
            logger()->error('[Zarinpal] cURL Error #:' . $err);
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    return true;
                }
            } else {
                logger()->error('[Zarinpal] Error Code: ' . $result['errors']['code'] . ' message: ' . $result['errors']['message']);
            }
        }

        return false;
    }

    public function refundPayment(string $transactionId, int $amount): array
    {
        return [];
    }
}
