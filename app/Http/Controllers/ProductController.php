<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Helpers\HtmlPurifierHelper;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with("images")->get();
        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with("images")->findOrFail($id);
        return response()->json($product);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            "image" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
        ]);

        if ($request->hasFile("image")) {
            $imagePath = $request->file("image")->store("product_images", "public");
            return response()->json(["image_name" => basename($imagePath)], 201);
        }

        return response()->json(["message" => "Image upload failed"], 400);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            "category_id" => "required|exists:categories,id",
            "brand_id" => "nullable|exists:brands,id",
            "title" => "required|string|max:255",
            "code" => "required|integer|unique:products",
            "price" => "required|numeric",
            "inventory" => "required|integer",
            "discount_percent" => "nullable|integer",
            "special_offer" => "boolean",
            "discount_rules" => "nullable|string",
            "description" => "required|string",
            "technical_description" => "nullable|string",
            "image_names" => "nullable|array",
            "image_names.*" => "string"
        ]);

        $data = $request->all();
        $data["description"] = HtmlPurifierHelper::clean($request->input("description"));
        $data["technical_description"] = HtmlPurifierHelper::clean($request->input("technical_description"));

        $product = Product::create($data);

        if ($request->has("image_names")) {
            foreach ($request->input("image_names") as $imageName) {
                ProductImage::create([
                    "product_id" => $product->id,
                    "image_path" => "product_images/" . $imageName
                ]);
            }
        }

        return response()->json($product->load("images"), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            "category_id" => "required|exists:categories,id",
            "brand_id" => "required|exists:brands,id",
            "title" => "required|string|max:255",
            "code" => "required|integer|unique:products,code," . $id,
            "price" => "required|numeric",
            "inventory" => "required|integer",
            "discount_percent" => "nullable|integer",
            "special_offer" => "boolean",
            "discount_rules" => "nullable|string",
            "description" => "required|string",
            "technical_description" => "nullable|string",
            "image_names" => "nullable|array",
            "image_names.*" => "string"
        ]);

        $product = Product::findOrFail($id);
        $data = $request->all();
        $data["description"] = HtmlPurifierHelper::clean($request->input("description"));
        $data["technical_description"] = HtmlPurifierHelper::clean($request->input("technical_description"));

        $product->update($data);

        if ($request->has("image_names")) {
            foreach ($product->images as $image) {
                Storage::disk("public")->delete($image->image_path);
                $image->delete();
            }
            foreach ($request->input("image_names") as $imageName) {
                ProductImage::create([
                    "product_id" => $product->id,
                    "image_path" => "product_images/" . $imageName
                ]);
            }
        }

        return response()->json($product->load("images"));
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        foreach ($product->images as $image) {
            Storage::disk("public")->delete($image->image_path);
            $image->delete();
        }
        $product->delete();
        return response()->json(null, 204);
    }
}
