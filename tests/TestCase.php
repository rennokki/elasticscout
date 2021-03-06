<?php

namespace Rennokki\ElasticScout\Tests;

use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rennokki\ElasticScout\ElasticScoutServiceProvider;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Tests\Models\Book;
use Rennokki\ElasticScout\Tests\Models\Post;
use Rennokki\ElasticScout\Tests\Models\Restaurant;

abstract class TestCase extends Orchestra
{
    /**
     * Set up the test case.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->resetDatabase();
        $this->resetCluster();
        $this->clearCache();

        $this->loadLaravelMigrations(['--database' => 'sqlite']);
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->withFactories(__DIR__.'/database/factories');

        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    /**
     * Get the package providers for the app.
     *
     * @param  mixed  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ElasticScoutServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix'   => '',
        ]);
        $app['config']->set('auth.providers.restaurants.model', Restaurant::class);
        $app['config']->set('auth.providers.posts.model', Post::class);
        $app['config']->set('auth.providers.books.model', Book::class);
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');

        $app['config']->set('elasticscout', [
            'connection' => [
                'hosts' => [
                    [
                        'host' => env('SCOUT_ELASTICSEARCH_HOST', '127.0.0.1'),
                        'port' => env('SCOUT_ELASTICSEARCH_PORT', 9200),
                        'scheme' => env('SCOUT_ELASTICSEARCH_SCHEME', null),
                        'user' => env('SCOUT_ELASTICSEARCH_USER', null),
                        'pass' => env('SCOUT_ELASTICSEARCH_PASSWORD', null),

                        'aws_enable' => env('ELASTICSCOUT_AWS_ENABLED', false),
                        'aws_region' => env('ELASTICSCOUT_AWS_REGION', 'us-east-1'),
                        'aws_key' => env('AWS_ACCESS_KEY_ID', ''),
                        'aws_secret' => env('AWS_SECRET_ACCESS_KEY', ''),
                    ],
                ],
            ],
            'indexer' => getenv('SCOUT_ELASTICSEARCH_INDEXER_CLASS') ?: env('SCOUT_ELASTICSEARCH_INDEXER_CLASS', \Rennokki\ElasticScout\Indexers\SimpleIndexer::class),
            'sync_mapping_on_save' => env('SCOUT_ELASTICSEARCH_SYNC_MAPPING_ON_SAVE', true),
            'refresh_document_on_save' => env('SCOUT_ELASTICSEARCH_REFRESH_DOCUMENT_ON_SAVE', false),
        ]);

        $app['config']->set('scout', [
            'driver' => env('SCOUT_DRIVER', 'algolia'),
            'prefix' => env('SCOUT_PREFIX', ''),
            'queue' => [
                'queue' => env('SCOUT_QUEUE', 'default'),
                'connection' => env('SCOUT_QUEUE_CONNECTION', 'redis'),
            ],
            'chunk' => [
                'searchable' => 500,
                'unsearchable' => 500,
            ],
            'soft_delete' => true,
        ]);
    }

    /**
     * Reset the database file.
     *
     * @return void
     */
    protected function resetDatabase(): void
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }

    /**
     * Delete the elasticsearch cluster indexes.
     *
     * @return void
     */
    protected function resetCluster(): void
    {
        ElasticClient::indices()->delete(['index' => '*']);
    }

    /**
     * Clear the cache.
     *
     * @return void
     */
    protected function clearCache()
    {
        $this->artisan('cache:clear');
    }
}
