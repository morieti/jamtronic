<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function arrangeFilters($filters): string
    {
        $filterQuery = [];
        foreach ($filters as $key => $filter) {
            if (empty($key) || is_numeric($key)) {
                $filterQuery[] = $filter;
            } else {
                $filterQuery[] = $key . ' = ' . $filter;
            }
        }

        return implode(' AND ', $filterQuery);
    }
}
