<?php

namespace Rennokki\ElasticScout;

use InvalidArgumentException;
use Elasticsearch\ClientBuilder;
use Laravel\Scout\EngineManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Rennokki\ElasticScout\Console\MigrateCommand;
use Rennokki\ElasticScout\Console\MakeRuleCommand;
use Rennokki\ElasticScout\Console\DropIndexCommand;
use Rennokki\ElasticScout\Console\CreateIndexCommand;
use Rennokki\ElasticScout\Console\UpdateIndexCommand;
use Rennokki\ElasticScout\Console\UpdateMappingCommand;
use Rennokki\ElasticScout\Console\MakeIndexCommand;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Ring\Future\CompletedFutureArray;

class ElasticScoutServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/elasticscout.php' => config_path('elasticscout.php'),
        ]);

        $this->commands([
            MakeIndexCommand::class,
            MakeRuleCommand::class,

            CreateIndexCommand::class,
            UpdateIndexCommand::class,
            DropIndexCommand::class,
            UpdateMappingCommand::class,
            MigrateCommand::class,
        ]);

        $this
            ->app
            ->make(EngineManager::class)
            ->extend('elasticscout', function () {
                $indexerType = config('elasticscout.indexer', 'simple');
                $updateMapping = config('elasticscout.update_mapping_on_save', true);
                $indexerClass = '\\Rennokki\\ElasticScout\\Indexers\\'.ucfirst($indexerType).'Indexer';

                if (! class_exists($indexerClass)) {
                    throw new InvalidArgumentException(sprintf(
                        'The %s indexer doesn\'t exist.',
                        $indexerType
                    ));
                }

                return new ElasticScoutEngine(new $indexerClass(), $updateMapping);
            });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this
            ->app
            ->singleton('elasticscout.client', function () {
                $connection = Config::get('elasticscout.connection', []);
                $clientBuilder = ClientBuilder::create();

                foreach ($connection['hosts'] as $host) {
                    if (isset($host['aws_enable']) && $host['aws_enable']) {
                        $clientBuilder->setHandler(function(array $request) use ($host) {
                            $psr7Handler = \Aws\default_http_handler();
                            $signer = new SignatureV4('es', $host['aws_region']);
                            $request['headers']['Host'][0] = parse_url($request['headers']['Host'][0])['host'];

                            // Create a PSR-7 request from the array passed to the handler
                            $psr7Request = new Request(
                                $request['http_method'],
                                (new Uri($request['uri']))
                                    ->withScheme($request['scheme'])
                                    ->withHost($request['headers']['Host'][0]),
                                $request['headers'],
                                $request['body']
                            );

                            // Sign the PSR-7 request with credentials from the environment
                            $signedRequest = $signer->signRequest(
                                $psr7Request,
                                new Credentials($host['aws_key'], $host['aws_secret'])
                            );

                            // Send the signed request to Amazon ES
                            /** @var \Psr\Http\Message\ResponseInterface $response */
                            $response = $psr7Handler($signedRequest)
                                ->then(function(\Psr\Http\Message\ResponseInterface $response) {
                                    return $response;
                                }, function($error) {
                                    return $error['response'];
                                })->wait();

                            // Convert the PSR-7 response to a RingPHP response
                            return new CompletedFutureArray([
                                'status' => $response->getStatusCode(),
                                'headers' => $response->getHeaders(),
                                'body' => $response->getBody()->detach(),
                                'transfer_stats' => ['total_time' => 0],
                                'effective_url' => (string)$psr7Request->getUri(),
                            ]);
                        });
                    }
                }

                return $clientBuilder->build();
            });
    }
}
