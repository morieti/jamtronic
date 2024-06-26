<?php

namespace App\Engines;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\MeilisearchEngine;

class CustomMeiliSearchEngine extends MeiliSearchEngine
{
    public function paginate(Builder $builder, $perPage, $page)
    {
        $options['limit'] = $perPage;
        $options['offset'] = ($page - 1) * $perPage;
        $builder->options = array_merge($builder->options, $options);

        $results = $this->performSearch($builder, array_filter([
            'filter' => $this->filters($builder),
            'sort' => $this->buildSortFromOrderByClauses($builder),
        ]));

        $results['totalHits'] = $results['nbHits'];
        return $results;
    }

    public function map(Builder $builder, $results, $model)
    {
        $totalHits = $results['nbHits'] ?? 0;
        $results = parent::map($builder, $results, $model);
        $results['totalHits'] = $totalHits;

        return $results;
    }
}
