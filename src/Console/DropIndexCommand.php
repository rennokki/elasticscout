<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\Command;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Migratable;
use Rennokki\ElasticScout\Console\Features\RequiresIndexArgument;
use Rennokki\ElasticScout\Payloads\RawPayload;

class DropIndexCommand extends Command
{
    use RequiresIndexArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elasticscout:index:drop';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Drop an Elasticsearch index.';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $index = $this->getIndex();
        $indexName = $this->resolveIndexName($index);

        $payload = (new RawPayload())
            ->set('index', $indexName)
            ->get();

        ElasticClient::indices()
            ->delete($payload);

        $this->info(sprintf(
            'The index %s was deleted!',
            $indexName
        ));
    }

    /**
     * @param  Index  $index
     * @return string
     */
    protected function resolveIndexName($index)
    {
        if (in_array(Migratable::class, class_uses_recursive($index))) {
            $payload = (new RawPayload())
                ->set('name', $index->getWriteAlias())
                ->get();

            $aliases = ElasticClient::indices()
                ->getAlias($payload);

            return key($aliases);
        } else {
            return $index->getName();
        }
    }
}
