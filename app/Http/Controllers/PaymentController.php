<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateway\SamanGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::whereHas('order', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->get();

        return response()->json($payments);
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with('order')->findOrFail($id);
        return response()->json($payment);
    }

    public function saman($token, $orderId)
    {
        $url = SamanGateway::START_PAYMENT_URL;

        $data = [
            'token' => $token,
            'RedirectURL' => route('payment.callback', ['orderId' => $orderId])
        ];

        return view('gateway-forward', compact('url', 'data'));
    }

    public function callback(Request $request, int $orderId): JsonResponse
    {
        /** @var Order $order */
        $order = Order::query()->with('items')->find($orderId);

        if (!$order) {
            return response()->json(['Order not found'], Response::HTTP_NOT_FOUND);
        }

        if ($order->status != Order::STATUS_PENDING_PAYMENT) {
            return response()->json(['Unprocessable Order'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $transactionId = $this->paymentService
            ->getPaymentGateway($order->payment_gateway, $order)
            ->getTransactionId($request);

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('payment_status', Payment::STATUS_PENDING)
            ->where('transaction_id', $transactionId)
            ->first();

        if (!$payment) {
            return response()->json(['Unprocessable Order'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userId = auth()->user()->id;
        try {
            DB::beginTransaction();

            $payment->update([
                'payment_status' => Payment::STATUS_WAITING_VERIFICATION,
            ]);

            $verify = $this->paymentService->verifyTransaction($order, $request);
            if ($verify) {
                $paymentStatus = Payment::STATUS_VERIFIED;
                $orderStatus = Order::STATUS_PAYMENT_SUCCESS;
                $response = 'Payment Successful';
            } else {
                $order->getBackInventories();
                $order->discount->returnDiscount($userId, $order->id);

                $paymentStatus = Payment::STATUS_FAILED;
                $orderStatus = Order::STATUS_PAYMENT_FAILED;
                $response = 'Payment Failed';
            }

            $payment->update([
                'payment_status' => $paymentStatus,
            ]);

            $order->transitionTo($orderStatus);
            $order->save();

            DB::commit();
        } catch (\Exception $exception) {
            $order->discount->returnDiscount($userId, $order->id);
            DB::rollBack();

            logger()->error($exception->getMessage());
            return response()->json(['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($response);
    }
}
