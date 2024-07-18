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
        'title',
        'code',
        'price',
        'inventory',
        'discount_percent',
        'special_offer',
        'discount_rules',
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
            'title' => $this->title,
            'code' => $this->code,
            'price' => (int)$this->price,
            'inventory' => (int)$this->inventory,
            'discount_percent' => (int)$this->discount_percent,
            'special_offer' => (int)$this->special_offer,
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

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
