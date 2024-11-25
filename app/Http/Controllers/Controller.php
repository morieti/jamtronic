<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function arrangeFilters($filters): string
    {
        $filterQuery = [];
        foreach ($filters as $key => $filter) {
            if (in_array($key, ['to', 'from'])) {
                if ($key == 'from') {
                    $fromTimestamp = strtotime($filter);
                    $filterQuery[] = "created_at >= $fromTimestamp";
                }

                if ($key == 'to') {
                    $toTimestamp = strtotime($filter);
                    $filterQuery[] = "created_at <= $toTimestamp";
                }
            } else {
                if (empty($key) || is_numeric($key)) {
                    $filterQuery[] = $filter;
                } else {
                    $filterQuery[] = $key . ' = ' . $filter;
                }
            }
        }

        return implode(' AND ', $filterQuery);
    }
}
