<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    // Region methods
    public function indexRegions(): JsonResponse
    {
        $regions = Region::with('cities')->get();
        return response()->json($regions);
    }

    public function showRegion(int $id): JsonResponse
    {
        $region = Region::with('cities')->findOrFail($id);
        return response()->json($region);
    }

    public function storeRegion(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $region = Region::create($request->only(['name']));
        return response()->json($region, 201);
    }

    public function updateRegion(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $region = Region::findOrFail($id);
        $region->update($request->only(['name']));
        return response()->json($region);
    }

    public function destroyRegion(int $id): JsonResponse
    {
        $region = Region::findOrFail($id);
        $region->delete();
        return response()->json(null, 204);
    }

    // City methods
    public function indexCities(Request $request): JsonResponse
    {
        $query = City::with('region');

        if ($request->has('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }

        $cities = $query->get();
        return response()->json($cities);
    }

    public function showCity(int $id): JsonResponse
    {
        $city = City::with('region')->findOrFail($id);
        return response()->json($city);
    }

    public function storeCity(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
        ]);

        $city = City::create($request->only(['region_id', 'name']));
        return response()->json($city, 201);
    }

    public function updateCity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
        ]);

        $city = City::findOrFail($id);
        $city->update($request->only(['region_id', 'name']));
        return response()->json($city);
    }

    public function destroyCity(int $id): JsonResponse
    {
        $city = City::findOrFail($id);
        $city->delete();
        return response()->json(null, 204);
    }
}
