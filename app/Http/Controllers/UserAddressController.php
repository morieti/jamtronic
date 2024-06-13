<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $addresses = UserAddress::where('user_id', $userId)->with(['region', 'city'])->get();
        return response()->json($addresses);
    }

    public function show(int $id): JsonResponse
    {
        $userId = auth()->id();
        $address = UserAddress::where('user_id', $userId)->with(['region', 'city'])->findOrFail($id);
        return response()->json($address);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_mobile' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
        ]);

        $user = $request->user();
        $address = UserAddress::create([
            'user_id' => $user->id,
            'region_id' => $request->input('region_id'),
            'city_id' => $request->input('city_id'),
            'receiver_name' => $request->input('receiver_name') ?? $user->full_name,
            'receiver_mobile' => $request->input('receiver_mobile') ?? $user->mobile,
            'address' => $request->input('address'),
            'postal_code' => $request->input('postal_code'),
            'description' => $request->input('description'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
        ]);

        return response()->json($address, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,id',
            'receiver_name' => 'required|string|max:255',
            'receiver_mobile' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
        ]);

        $user = $request->user();
        $address = UserAddress::where('user_id', $user->id)->findOrFail($id);

        $data = [
            'user_id' => $user->id,
            'region_id' => $request->input('region_id'),
            'city_id' => $request->input('city_id'),
            'receiver_name' => $request->input('receiver_name') ?? $user->full_name,
            'receiver_mobile' => $request->input('receiver_mobile') ?? $user->mobile,
            'address' => $request->input('address'),
            'postal_code' => $request->input('postal_code'),
            'description' => $request->input('description'),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
        ];

        $address->update($data);
        return response()->json($address);
    }

    public function destroy(int $id): JsonResponse
    {
        $userId = auth()->id();
        $address = UserAddress::where('user_id', $userId)->findOrFail($id);
        $address->delete();

        return response()->json(null, 204);
    }
}
