<?php

namespace Rennokki\ElasticScout\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rennokki\ElasticScout\ElasticScoutServiceProvider;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Tests\Models\Book;
use Rennokki\ElasticScout\Tests\Models\Post;
use Rennokki\ElasticScout\Tests\Models\Restaurant;

abstract class TestCase extends Orchestra
{
    /**
     * The models whose indices will be flushed.
     *
     * @var array
     */
    protected static $models = [
        Book::class, Restaurant::class, Post::class,
    ];

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
                        'host' => '127.0.0.1',
                        'port' => 9200,
                        'scheme' => null,
                        'user' => null,
                        'pass' => null,
                        'aws_enable' => false,
                        'aws_region' => 'us-east-1',
                        'aws_key' => '',
                        'aws_secret' => '',
                    ],
                ],
            ],
            'indexer' => 'simple',
            'update_mapping_on_save' => true,
            'refresh_document_on_save' => true,
        ]);
    }

    /**
     * Reset the database file.
     *
     * @return void
     */
    protected function resetDatabase()
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }

    protected function resetCluster()
    {
        foreach (self::$models as $model) {
            (new $model)->getIndex()->delete();
        }
    }
}
