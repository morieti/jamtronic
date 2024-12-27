<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiscountController extends Controller
{
    public function index(): JsonResponse
    {
        $discounts = Discount::all();
        return response()->json($discounts);
    }

    public function show(int $id): JsonResponse
    {
        $discount = Discount::query()->findOrFail($id);
        return response()->json($discount);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:discounts',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
            'min_order_value' => 'required|numeric',
            'usage_limit' => 'required|numeric',
            'per_user_limit' => 'required|numeric',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date',
            'is_active' => 'required|boolean',
            'is_free_shipping' => 'required|boolean',
        ]);

        $discount = Discount::query()->create($request->all());
        return response()->json($discount, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'code' => 'nullable|string|max:255|unique:discounts,code,' . $id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
            'min_order_value' => 'required|numeric',
            'usage_limit' => 'required|numeric',
            'per_user_limit' => 'required|numeric',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date',
            'is_active' => 'required|boolean',
            'is_free_shipping' => 'required|boolean',
        ]);

        $discount = Discount::findOrFail($id);
        $discount->update($request->all());

        return response()->json($discount);
    }

    public function destroy(int $id): JsonResponse
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();
        return response()->json(null, 204);
    }
}
