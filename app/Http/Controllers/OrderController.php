<?php

namespace App\Http\Controllers;

use App\Events\WalletBalanceUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\UserAddress;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.payable', 'items.payable.images', 'shippingMethod', 'userAddress', 'payments'])
            ->get();
        return response()->json($orders);
    }

    public function search(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search', '');
        $from = $request->input('from', '');
        $to = $request->input('to', '');

        $perPage = (int)$request->input('size', 20);
        $page = (int)$request->input('page', 1);

        $filters = $request->except(['search', 'size', 'page', 'from', 'to'], []);

        $filterQuery = $this->arrangeFilters($filters);

        $orders = Order::search($searchQuery)
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            });

        if ($from) {
            $orders = $orders->where('created_at', '>=', $from);
        }

        if ($to) {
            $orders = $orders->where('created_at', '<=', $to);
        }

        $orders = $orders->paginate($perPage, 'page', $page);

        $orders = $orders->jsonSerialize();
        unset($orders['data']['totalHits']);

        return response()->json($orders);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::query()
            ->with(['items', 'items.payable', 'items.payable.images', 'shippingMethod', 'userAddress', 'payments'])
            ->whereRelation('items', 'payable_type','=',Product::class)
            ->findOrFail($id);

        $order->total_cart_price = $order->getCartPrice();
        $order->shipping_price = optional($order->shippingMethod)->price ?? 0;

        return response()->json($order);
    }

    public function getOpenOrder(): JsonResponse
    {
        $openOrder = Order::query()
            ->with(['items.payable', 'shippingMethod', 'userAddress', 'payments'])
            ->where('user_id', auth()->user()->id)
            ->whereIn('status', [Order::STATUS_CHECKOUT, Order::STATUS_PENDING_PAYMENT])
            ->firstOrFail();

        return response()->json($openOrder);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $previousOrder = Order::query()
            ->where('user_id', auth()->user()->id)
            ->whereIn('status', [Order::STATUS_CHECKOUT, Order::STATUS_PENDING_PAYMENT])
            ->first();

        if ($previousOrder && $previousOrder->id) {
            $data = [
                'previous_order_id' => $previousOrder->id,
                'message' => 'Please Complete Previous Orders'
            ];

            return response()->json($data, Response::HTTP_CONFLICT);
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => auth()->user()->id,
                'status' => Order::STATUS_CHECKOUT,
            ]);

            foreach ($request->input('items') as $item) {
                /** @var Product $product */
                $product = Product::query()->find($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'payable_id' => $product->id,
                    'payable_type' => Product::class,
                    'quantity' => $item['quantity'],
                    'price' => $this->paymentService->calcProductPrice($product, $item['quantity']),
                ]);

                $product->inventory -= $item['quantity'];
                $product->item_sold += $item['quantity'];
                $product->save();
            }
            DB::commit();

            return response()->json($order->load(['items.payable']), Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error($exception->getMessage());
            return response()->json(['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPaymentMethods(): JsonResponse
    {
        return response()->json($this->paymentService->getAllPaymentGateways());
    }

    public function update(Request $request, int $id): Response
    {
        $request->validate([
            'user_address_id' => 'nullable',
            'shipping_method_id' => 'nullable',
            'payment_gateway' => 'nullable|string|max:20|in:saman,zarinpal',
            'use_wallet' => 'nullable|boolean',
        ]);

        /** @var Order $order */
        $order = Order::query()->with('items')->findOrFail($id);

        if ($request->input('user_address_id')) {
            /** @var UserAddress $userAddress */
            $userAddress = UserAddress::query()->find($request->input('user_address_id'));

            if ($userAddress->user_id != $request->user()->id) {
                return response()->json(['User Address Forbidden'], Response::HTTP_FORBIDDEN);
            }

            $order->update([
                'user_address_id' => $userAddress->id,
                'short_address' => $userAddress->address,
            ]);
        }

        if ($request->input('shipping_method_id')) {
            if (!isset($userAddress)) {
                /** @var UserAddress $userAddress */
                $userAddress = $order->userAddress;
            }

            /** @var ShippingMethod $shippingMethod */
            $shippingMethod = ShippingMethod::query()->find($request->input('shipping_method_id'));

            if (!$shippingMethod || !in_array($shippingMethod->id, ShippingMethod::getFlatActiveMethods($userAddress))) {
                return response()->json(['Shipping Method Forbidden'], Response::HTTP_FORBIDDEN);
            }

            try {
                DB::beginTransaction();

                if ($shippingMethod->price > 0) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'payable_id' => $shippingMethod->id,
                        'payable_type' => ShippingMethod::class,
                        'quantity' => 1,
                        'price' => $shippingMethod->price,
                    ]);
                }

                $order->update([
                    'shipping_method_id' => $shippingMethod->id,
                    'short_shipping_data' => $shippingMethod->getShippingDetailString(),
                ]);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                logger()->error($exception->getMessage());
                return response()->json(['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        if ($request->input('payment_gateway')) {
            try {
                DB::beginTransaction();

                $useWallet = $request->input('use_wallet', false);
                $priceData = $this->paymentService->calcOrderPrice($order, $useWallet);

                $price = $priceData['orderPrice'];
                $walletPrice = $priceData['walletPrice'];

                $order->fill([
                    'total_price' => $price,
                    'wallet_price_used' => $walletPrice,
                    'payment_gateway' => $request->input('payment_gateway'),
                    'use_wallet' => $useWallet,
                ]);
                $order->transitionTo(Order::STATUS_PENDING_PAYMENT);

                $order->user->wallet_balance -= $walletPrice;

                $order->user->save();
                $order->save();

                if ($order->total_price > 0) {
                    $url = $this->paymentService->initTransaction($order);
                } else {
                    $order->transitionTo(Order::STATUS_PAYMENT_SUCCESS);
                    $order->save();
                }

                DB::commit();
                event((new WalletBalanceUpdated($order->user))->setOrder($order));

                return redirect()->to($url);
            } catch (\Exception $exception) {
                DB::rollBack();
                logger()->error($exception->getMessage());
                return response()->json(['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json($order->load(['items.payable', 'shippingMethod', 'userAddress', 'payments']));
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        $order = Order::query()->findOrFail($id);
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::$states),
        ]);

        $state = $request->input('status');
        if (!$order->canTransitionTo($state)) {
            return response()->json(['Order Status Forbidden'], Response::HTTP_FORBIDDEN);
        }
        $order->update(['status' => $state]);
        return response()->json(
            $order->load(['items', 'items.payable', 'items.payable.images', 'shippingMethod', 'userAddress', 'payments'])
        );
    }

    public function cancelOrder(int $id): JsonResponse
    {
        /** @var Order $order */
        $order = Order::query()->with('items')->findOrFail($id);

        if ($order->user_id != auth()->user()->id) {
            return response()->json(['Order Not Found'], Response::HTTP_FORBIDDEN);
        }

        if (!in_array($order->status, [Order::STATUS_PENDING_PAYMENT, Order::STATUS_CHECKOUT])) {
            return response()->json(['Order Is Not Cancellable'], Response::HTTP_FORBIDDEN);
        }

        try {
            DB::beginTransaction();
            $order->transitionTo(Order::STATUS_USER_CANCELED);
            $order->getBackInventories();
            $order->save();
            DB::commit();

            return response()->json('Order Cancelled Successfully', Response::HTTP_OK);
        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error($exception->getMessage());
            return response()->json(['Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
