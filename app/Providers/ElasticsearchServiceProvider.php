<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Transport\NodePool\NodePoolInterface;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts([env('ELASTICSEARCH_HOST', 'localhost') . ':' . env('ELASTICSEARCH_PORT', '9200')])
                ->build();
        });

        $this->app->bind(NodePoolInterface::class, function ($app) {
            return $app->make(Client::class)->transport()->getNodePool();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
