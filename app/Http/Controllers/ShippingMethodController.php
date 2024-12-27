<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingMethodController extends Controller
{
    public function index(UserAddress $userAddress): JsonResponse
    {
        $shippingMethods = ShippingMethod::getSelectableMethodsList($userAddress);
        return response()->json($shippingMethods);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:express,express_option,post,in_person',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'price_caption' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:shipping_methods,id',
            'status' => 'required|in:active,inactive',
            'capacity' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $shippingMethod = ShippingMethod::create($request->all());
        return response()->json($shippingMethod, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:express,express_option,post,in_person',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'price_caption' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:shipping_methods,id',
            'status' => 'required|in:active,inactive',
            'capacity' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $shippingMethod = ShippingMethod::findOrFail($id);
        $shippingMethod->update($request->all());

        return response()->json($shippingMethod);
    }
}
