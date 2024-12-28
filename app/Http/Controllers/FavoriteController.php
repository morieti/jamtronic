<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $favorites = $user
            ->favorites()
            ->with('product', 'product.images')
            ->where('expires_at', '>', now())
            ->get();

        return response()->json($favorites);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $user = $request->user();
        $productIds = $request->input('product_ids');
        $productIds = array_chunk($productIds, 50)[0];

        $favorites = [];
        foreach ($productIds as $productId) {
            try {
                $favorites[] = UserFavorite::query()->create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'expires_at' => now()->addMonth()
                ]);
            } catch (\Throwable $exception) {
                return response()->json(['Duplicate favorite'], 400);
            }
        }

        return response()->json($favorites, 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favorite = UserFavorite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $request->input('product_id'))
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([], 204);
        }

        return response()->json(['message' => 'Favorite not found'], 404);
    }
}
