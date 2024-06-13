<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    public function show(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        return response()->json($brand);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands',
        ]);

        $brand = Brand::create($request->all());
        return response()->json($brand, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $id,
        ]);

        $brand = Brand::findOrFail($id);
        $brand->update($request->all());
        return response()->json($brand);
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return response()->json(null, 204);
    }
}
