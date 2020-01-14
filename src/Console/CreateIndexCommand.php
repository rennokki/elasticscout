<?php

namespace Rennokki\ElasticScout\Console;

use Rennokki\ElasticScout\Migratable;
use Illuminate\Console\Command;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Payloads\IndexPayload;
use Rennokki\ElasticScout\Console\Features\RequiresIndexArgument;

class CreateIndexCommand extends Command
{
    use RequiresIndexArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elasticscout:index:create';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create an Elasticsearch index.';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createIndex();
        $this->createWriteAlias();
    }

    /**
     * Create an index.
     *
     * @return void
     */
    protected function createIndex()
    {
        $index = $this->getIndex();

        $payload = (new IndexPayload($index))
            ->setIfNotEmpty('body.settings', $index->getSettings())
            ->get();

        ElasticClient::indices()
            ->create($payload);

        $this->info(sprintf(
            'The %s index was created!',
            $index->getName()
        ));
    }

    /**
     * Create an write alias.
     *
     * @return void
     */
    protected function createWriteAlias()
    {
        $index = $this->getIndex();

        if (! in_array(Migratable::class, class_uses_recursive($index))) {
            return;
        }

        $payload = (new IndexPayload($index))
            ->set('name', $index->getWriteAlias())
            ->get();

        ElasticClient::indices()
            ->putAlias($payload);

        $this->info(sprintf(
            'The %s alias for the %s index was created!',
            $index->getWriteAlias(),
            $index->getName()
        ));
    }
}
