<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'category_id',
        'brand_id',
        'tag_id',
        'title',
        'code',
        'price',
        'item_sold',
        'inventory',
        'discount_percent',
        'special_offer_price',
        'discount_rules',
        'sheet_file',
        'short_description',
        'description',
        'technical_description',
        'faq',
        'images',
        'category',
        'brand'
    ];

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'products_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int)$this->id,
            'category_id' => (int)$this->category_id,
            'brand_id' => (int)$this->brand_id,
            'tag_id' => (int)$this->tag_id,
            'title' => $this->title,
            'code' => $this->code,
            'price' => (int)$this->price,
            'inventory' => (int)$this->inventory,
            'discount_percent' => (int)$this->discount_percent,
            'special_offer_price' => (int)$this->special_offer_price,
            'sheet_file' => $this->sheet_file,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'technical_description' => $this->technical_description,
            'faq' => $this->faq,
            'images' => $this->images,
            'category' => $this->category,
            'brand' => $this->brand,
        ];
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function faved(int $userId): HasMany
    {
        return $this->hasMany(UserFavorite::class)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now());
    }

    public function getBreadCrumb(): array
    {
        $layers = [
            [
                'title' => __('Home'),
                'url' => env('APP_URL')
            ]
        ];

        $categoryList = $this->category->getGenealogy();
        foreach ($categoryList as $category) {
            $layers[] = [
                'title' => $category->name,
                'url' => env('APP_URL') . '/product-category/' . $category->getCategoryLineSlugs() . '?category_id=' . $category->id,
            ];
        }

        return $layers;
    }
}
