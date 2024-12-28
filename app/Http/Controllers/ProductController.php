<?php

namespace App\Http\Controllers;

use App\Helpers\HtmlPurifierHelper;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(): JsonResponse
    {
        $products = Product::with('images')->get();
        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('images')->findOrFail($id);

        $images = [];
        foreach ($product->images as $image) {
            $imagePath = explode('/', $image->image_path);
            $image['name'] = end($imagePath);
            $images[] = $image;
        }
        $product->images = $images;
        $userId = optional(auth()->user())->id;
        $product->faved = $product->userFaved($userId)->count();
        $product->special_offer = $product->getSpecialOffer();

        $product->breadcrumb = $product->getBreadcrumb();
        return response()->json($product);
    }

    public function searchSuggestion(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        if (!$query) {
            return response()->json([]);
        }

        $suggestions = Product::search($query)->take(5)->get()->pluck('title');

        return response()->json($suggestions);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $perPage = (int)$request->input('size', 20);
        $page = (int)$request->input('page', 1);

        $filters = $request->except(['q', 'size', 'page'], []);

        $priceFilter = $filters['price'] ?? null;
        if ($priceFilter) {
            $filters[] = $this->productService->setPriceFilter($priceFilter);
            unset($filters['price']);
        }

        $brandFilter = $filters['brand_id'] ?? null;
        if ($brandFilter) {
            $filters[] = $this->productService->setBrandFilter($brandFilter);
            unset($filters['brand_id']);
        }

        $available = $filters['is_available'] ?? null;
        if (!is_null($available)) {
            $filters[] = $this->productService->setAvailabilityFilter($available);
            unset($filters['is_available']);
        }

        $special = $filters['special_offer'] ?? null;
        if (!is_null($special)) {
            $filters[] = $this->productService->setSpecialOfferFilter($special);
            unset($filters['special_offer']);
        }

        $filterQuery = $this->arrangeFilters($filters);
        $userId = optional(auth()->user())->id;

        $products = Product::search($query)
            ->query(function ($query) {
                $query->with('images', 'category', 'brand', 'tag');
            })
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            })
            ->paginate($perPage, 'page', $page)
            ->through(function ($product) use ($userId) {
                /** @var Product $product */
                $product->faved = $product->userFaved($userId)->count();
                $product->special_offer = $product->getSpecialOffer();
                return $product;
            });

        $products = $products->jsonSerialize();
        unset($products['data']['totalHits']);

        return response()->json($products);
    }

    public function related(Request $request, int $productId): JsonResponse
    {
        $product = Product::query()->findOrFail($productId);
        $size = (int)$request->input('size', 6);

        $categories = [$product->category_id];
        if ($product->category->parent_id) {
            $categories[] = $product->category->parent_id;
        }
        $categories = implode(',', $categories);

        $priceRange = round($product->price * 0.7) . ',' . round($product->price * 1.3);

        $filters = [
            $this->productService->setCategoryFilter($categories),
            $this->productService->setPriceFilter($priceRange),
            $this->productService->setAvailabilityFilter(true),
            'id != ' . $product->id,
        ];

        $filterQuery = $this->arrangeFilters($filters);

        $relatedProducts = Product::search('')
            ->query(function ($query) {
                $query->with('images', 'category', 'brand');
            })
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            })
            ->paginate($size, 'page', 1);

        $relatedProducts = $relatedProducts->jsonSerialize();
        unset($relatedProducts['data']['totalHits']);

        return response()->json($relatedProducts);
    }

    public function bestSellerProducts(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('size', 10);

        $products = Product::query()
            ->with("images")
            ->orderBy('item_sold', 'DESC')
            ->limit($perPage)
            ->get();

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
            "special_offer_price" => "nullable|numeric",
            "discount_rules" => "required|string",
            "sheet_file" => "nullable|string",
            "short_description" => "required|string",
            "description" => "required|string",
            "technical_description" => "nullable|string",
            "faq" => "nullable|string",
            "image_names" => "nullable|array",
            "image_names.*" => "string"
        ]);

        $data = $request->except(['image_names']);
        try {
            json_decode($data['discount_rules']);
        } catch (\Throwable $e) {
            logger()->error($e);
            return response()->json('Discount rules not valid', 400);
        }

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

    public function uploadDataSheet(Request $request): JsonResponse
    {
        $request->validate([
            "sheet" => "required|file|mimes:pdf|max:2048",
        ]);

        if ($request->hasFile("sheet")) {
            $filePath = $request->file("sheet")->store("product_images", "public");
            return response()->json(["sheet_file_name" => basename($filePath)], 201);
        }

        return response()->json(["message" => "File upload failed"], 400);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            "category_id" => "nullable|exists:categories,id",
            "brand_id" => "nullable|exists:brands,id",
            "title" => "nullable|string|max:255",
            "code" => "nullable|integer|unique:products,code," . $id,
            "price" => "nullable|numeric",
            "inventory" => "nullable|integer",
            "discount_percent" => "nullable|integer",
            "special_offer_price" => "nullable|numeric",
            "discount_rules" => "nullable|string",
            "sheet_file" => "nullable|string",
            "short_description" => "nullable|string",
            "description" => "nullable|string",
            "technical_description" => "nullable|string",
            "faq" => "nullable|string",
            "image_names" => "nullable|array",
            "image_names.*" => "string"
        ]);

        $product = Product::findOrFail($id);
        $data = $request->all();
        try {
            if (isset($data['discount_rules'])) {
                json_decode($data['discount_rules']);
            }

            if (isset($data['description'])) {
                $data["description"] = HtmlPurifierHelper::clean($request->input("description"));
            }

            if (isset($data['technical_description'])) {
                $data["technical_description"] = HtmlPurifierHelper::clean($request->input("technical_description"));
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return response()->json('Discount rules not valid', 400);
        }

        $product->update($data);

        if ($request->has('sheet_file')) {
            Storage::disk('public')->delete($product->sheet_file);
        }

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
