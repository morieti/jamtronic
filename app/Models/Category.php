<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'image_alt',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getGenealogy(): array
    {
        $result = [];
        $category = clone $this;
        while ($category->parent_id) {
            $result[] = $category;
            $category = $category->parent;
        }
        $result[] = $category;

        return array_reverse($result);
    }

    public function getCategoryLineSlugs(): string
    {
        $result = $this->getGenealogy();
        $slugs = [];
        foreach ($result as $item) {
            $slugs[] = $item->slug;
        }

        return implode('/', $slugs);
    }
}
