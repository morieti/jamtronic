<?php

namespace App\Http\Controllers;

use App\Helpers\HtmlPurifierHelper;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $images = [];
        foreach ($product->images as $image) {
            $imagePath = explode('/', $image->image_path);
            $image['name'] = end($imagePath);
            $images[] = $image;
        }
        $product->images = $images;
        return response()->json($product);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $perPage = (int)$request->input('size', 20);
        $page = (int)$request->input('page', 1);

        $filters = $request->except(['q', 'size', 'page'], []);

        $priceFilter = $filters['price'] ?? null;
        if ($priceFilter) {
            $priceRange = explode(',', $priceFilter);
            if (isset($priceRange[0]) && $priceRange[0] !== '') {
                $filters[] = 'price >= ' . $priceRange[0];
            }
            if (isset($priceRange[1]) && $priceRange[1] !== '') {
                $filters[] = 'price <= ' . $priceRange[1];
            }
            unset($filters['price']);
        }

        $brandFilter = $filters['brand_id'] ?? null;
        if ($brandFilter) {
            $brandFilterPcs = explode(',', $brandFilter);
            $brandFilter = [];
            foreach ($brandFilterPcs as $brandFilterPc) {
                $brandFilter[] = 'brand_id = ' . $brandFilterPc;
            }
            $filters[] = '(' . implode(' OR ', $brandFilter) . ')';
            unset($filters['brand_id']);
        }

        $available = $filters['is_available'] ?? null;
        if (!is_null($available)) {
            $filters[] = 'inventory > ' . ($available ? 0 : -1);
            unset($filters['is_available']);
        }

        $filterQuery = [];
        foreach ($filters as $key => $filter) {
            if (empty($key)) {
                $filterQuery[] = $filter;
            } else {
                $filterQuery[] = $key . ' = ' . $filter;
            }
        }

        $filterQuery = implode(' AND ', $filterQuery);

        $products = Product::search($query)
            ->query(function ($query) {
                $query->with('images', 'category', 'brand');
            })
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            })
            ->paginate($perPage, 'page', $page);

        $products = $products->jsonSerialize();
        unset($products['data']['totalHits']);

        return response()->json($products);
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
            "discount_rules" => "required|array",
            "description" => "required|string",
            "technical_description" => "nullable|string",
            "faq" => "nullable|string",
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
                    "image_path" => "storage/product_images/" . $imageName
                ]);
            }
        }

        return response()->json($product->load("images"), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            "category_id" => "required|exists:categories,id",
            "brand_id" => "nullable|exists:brands,id",
            "title" => "required|string|max:255",
            "code" => "required|integer|unique:products,code," . $id,
            "price" => "required|numeric",
            "inventory" => "required|integer",
            "discount_percent" => "nullable|integer",
            "special_offer" => "boolean",
            "discount_rules" => "required|array",
            "description" => "required|string",
            "technical_description" => "nullable|string",
            "faq" => "nullable|string",
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
                    "image_path" => "storage/product_images/" . $imageName
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
