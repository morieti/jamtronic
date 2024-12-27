<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(string $type = ''): JsonResponse
    {
        $banners = Banner::query();
        if ($type) {
            $banners = $banners->where('type', $type);
        }
        $banners = $banners->get();
        return response()->json($banners);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
            return response()->json(["image_name" => basename($imagePath)], 201);
        }

        return response()->json(['message' => 'Image upload failed'], 400);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:banners',
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:255',
            'image' => 'required|string',
            'link' => 'required|string|max:255',
            'status' => 'required|boolean',
            'type' => 'required|in:top,bottom'
        ]);

        $data = $request->only(['slug', 'title', 'subtitle', 'image', 'link', 'type']);
        if ($data['image']) {
            $data['image'] = 'storage/banners' . $data['image'];
        } else {
            return response()->json(['Image is Required'], 400);
        }

        $banner = Banner::query()->create($data);
        if ($request->has('status')) {
            $banner->setStatus($request->get('status'));
        }

        return response()->json($banner, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'slug' => 'nullable|string|max:255|unique:banners,slug,' . $id,
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'type' => 'required|in:top,bottom'
        ]);

        $banner = Banner::findOrFail($id);

        $data = $request->only(['slug', 'title', 'subtitle', 'image', 'link', 'type']);
        if ($request->has('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = 'storage/banners/' . $data['image'];
        }

        $banner->update($data);
        if ($request->has('status')) {
            $banner->setStatus($request->get('status'));
        }

        return response()->json($banner);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        return response()->json(null, 204);
    }
}
