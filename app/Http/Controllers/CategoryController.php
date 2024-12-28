<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->input('main', 0) == 1) {
            $categories = Category::query()
                ->whereNull('parent_id')
                ->orderBy('order', 'ASC')
                ->get();
        } else {
            $categories = Category::query()
                ->orderBy('order', 'ASC')
                ->get();
        }
        return response()->json($categories);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('category_images', 'public');
            return response()->json(["image_name" => basename($imagePath)], 201);
        }

        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'image_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_on_menu' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'slug', 'parent_id', 'image_name', 'is_on_menu', 'order']);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'parent_id' => $data['parent_id'],
            'image' => isset($data['image_name']) ? ("storage/category_images/" . $data['image_name']) : '',
            'is_on_menu' => $data['is_on_menu'],
            'order' => $data['order'],
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'image_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_on_menu' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $category = Category::findOrFail($id);
        $data = $request->only(['name', 'slug', 'parent_id', 'image_name', 'is_on_menu', 'order']);

        if (isset($data['image_name'])) {
            // Delete the old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->image = "storage/category_images/" . $data['image_name'];
        }

        $category->name = $data['name'] ?? $category->name;
        $category->slug = $data['slug'] ?? $category->slug;
        $category->parent_id = $data['parent_id'] ?? $category->parent_id;
        $category->is_on_menu = $data['is_on_menu'] ?? $category->is_on_menu;
        $category->order = $data['order'] ?? $category->order;
        $category->save();

        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::query()->findOrFail($id);
        if ($category->products()->count() > 0) {
            return response()->json(['Category has product attached'], 400);
        }
        $category->delete();
        return response()->json(null, 204);
    }
}
