<?php

namespace Rennokki\ElasticScout\Console;

use Exception;
use LogicException;
use Rennokki\ElasticScout\Migratable;
use Illuminate\Console\Command;
use Rennokki\ElasticScout\Payloads\RawPayload;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Payloads\IndexPayload;
use Rennokki\ElasticScout\Console\Features\RequiresIndexArgument;

class UpdateIndexCommand extends Command
{
    use RequiresIndexArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elasticscout:index:update';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update settings and mappings of an Elasticsearch index.';

    /**
     * Handle the command.
     *
     * @var string
     */
    public function handle()
    {
        $this->updateIndex();
        $this->createWriteAlias();
    }

    /**
     * Update the index.
     *
     * @throws \Exception
     * @return void
     */
    protected function updateIndex()
    {
        $index = $this->getIndex();
        $indexPayload = (new IndexPayload($index))->get();
        $indices = ElasticClient::indices();

        if (!$indices->exists($indexPayload)) {
            throw new LogicException(sprintf(
                'Index %s doesn\'t exist',
                $index->getName()
            ));
        }

        try {
            $indices->close($indexPayload);

            if ($settings = $index->getSettings()) {
                $indexSettingsPayload = (new IndexPayload($index))
                    ->set('body.settings', $settings)
                    ->get();

                $indices->putSettings($indexSettingsPayload);
            }

            $indices->open($indexPayload);
        } catch (Exception $exception) {
            $indices->open($indexPayload);

            throw $exception;
        }

        $this->info(sprintf(
            'The index %s was updated!',
            $index->getName()
        ));
    }

    /**
     * Create a write alias, so the index
     * can be easily migratable.
     *
     * @return void
     */
    protected function createWriteAlias()
    {
        $index = $this->getIndex();

        if (!in_array(Migratable::class, class_uses_recursive($index))) {
            return;
        }

        $indices = ElasticClient::indices();

        $existsPayload = (new RawPayload())
            ->set('name', $index->getWriteAlias())
            ->get();

        if ($indices->existsAlias($existsPayload)) {
            return;
        }

        $putPayload = (new IndexPayload($index))
            ->set('name', $index->getWriteAlias())
            ->get();

        $indices->putAlias($putPayload);

        $this->info(sprintf(
            'The %s alias for the %s index was created!',
            $index->getWriteAlias(),
            $index->getName()
        ));
    }
}
