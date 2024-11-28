<?php

namespace App\Services;

class ProductService
{
    public function setPriceFilter($priceFilter): string
    {
        $priceRange = explode(',', $priceFilter);
        if (isset($priceRange[0]) && $priceRange[0] !== '') {
            return 'price >= ' . $priceRange[0];
        }

        if (isset($priceRange[1]) && $priceRange[1] !== '') {
            return 'price <= ' . $priceRange[1];
        }

        return '';
    }

    public function setBrandFilter($brands): string
    {
        $brandFilterPcs = explode(',', $brands);
        $brandFilter = [];
        foreach ($brandFilterPcs as $brandFilterPc) {
            $brandFilter[] = 'brand_id = ' . $brandFilterPc;
        }
        return '(' . implode(' OR ', $brandFilter) . ')';
    }

    public function setAvailabilityFilter($isAvailable): string
    {
        return 'inventory > ' . ($isAvailable ? 0 : -1);
    }

    public function setCategoryFilter($categories): string
    {
        $categoryFilterPcs = explode(',', $categories);
        $categoryFilter = [];
        foreach ($categoryFilterPcs as $categoryFilterPc) {
            $categoryFilter[] = 'category_id = ' . $categoryFilterPc;
        }
        return '(' . implode(' OR ', $categoryFilter) . ')';
    }
}
