<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $f = fopen(base_path() . '/database/seeders/products.csv', 'r');
        fgetcsv($f);
        while (!feof($f)) {
            $row = fgetcsv($f);
            if (!$row) {
                continue;
            }

            $categoryText = explode(',', $row[26])[0];
            $categoryText = explode('>', $categoryText);
            $categoryText = trim(end($categoryText));

            $category = Category::query()->where('name', $categoryText)->first();

            $tagId = null;
            if ($row[27]) {
                $tag = Tag::query()->where('name', $row[27])->first();
                $tagId = $tag->id;
            }

            $discountRules = [];
            $rules = $row[42] ? explode(',', $row[42]) : [];
            foreach ($rules as $rule) {
                $pcs = explode(':', trim($rule));
                $discountRules[$pcs[0]] = $pcs[1];
            }

            $sheetFile = '';
            if (!empty($row[83])) {
                $sheetFile = explode('"', $row[83]);
                if (count($sheetFile) > 1) {
                    $sheetFile = $sheetFile[1];
                }
            }

            try {
                $product = Product::query()->create([
                    'id' => $row[0],
                    'category_id' => $category->id,
                    'tag_id' => $tagId,
                    'title' => $row[3],
                    'code' => $row[2] ? (int)$row[2] : null,
                    'price' => (int)$row[25],
                    'inventory' => $row[14],
                    'discount_percent' => floor((int)$row[24] * 100 / (int)$row[25]),
                    'special_offer_price' => (int)$row[24],
                    'discount_rules' => $discountRules ? json_encode($discountRules) : null,
                    'sheet_file' => $this->getImagePath($sheetFile, $row[0]),
                    'short_description' => $row[7],
                    'description' => $row[8],
                    'technical_description' => $row[81],
                    'faq' => $row[85],
                ]);

                $images = explode(',', $row[29]);
                foreach ($images as $image) {
                    $imagePath = $this->getImagePath($image, $product->id);
                    if ($imagePath) {
                        ProductImage::query()->create([
                            "product_id" => $product->id,
                            "image_path" => $imagePath
                        ]);
                    }
                }

            } catch (\Throwable $exception) {
                dd($exception->getMessage(), $exception->getLine(), $row[0]);
            }
        }

    }

    /**
     * @param string $url
     * @return string|null
     */
    protected function getImagePath(string $url, int $id): ?string
    {
        try {
            $content = file_get_contents(trim($url));
            $fileName = explode('/', $url);
            $fileName = end($fileName);

            $tmpPath = '/tmp/' . $fileName;
            file_put_contents($tmpPath, $content);

            $file = new UploadedFile($tmpPath, $fileName);
            $imagePath = $file->store('product_images', 'public');
            $imagePath = 'storage/' . $imagePath;
        } catch (\Throwable $exception) {
            logger()->error("Product {$id} Does not retrieved!!! {$exception->getMessage()}");
            $imagePath = null;
        }

        return $imagePath;
    }
}
