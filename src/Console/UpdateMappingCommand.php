<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\Command;
use LogicException;
use Rennokki\ElasticScout\Console\Features\RequiresModelArgument;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Migratable;
use Rennokki\ElasticScout\Payloads\TypePayload;

class UpdateMappingCommand extends Command
{
    use RequiresModelArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elasticscout:mapping:update';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update a model mapping.';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $model = $this->getModel()) {
            return;
        }

        $index = $model->getIndex();
        $mapping = $index->getMapping();

        if (empty($mapping)) {
            throw new LogicException('Nothing to update: the mapping is not specified.');
        }

        $payload = (new TypePayload($model))
            ->set('body.'.$model->searchableAs(), $mapping)
            ->set('include_type_name', 'true');

        if (in_array(Migratable::class, class_uses_recursive($index))) {
            $payload->useAlias('write');
        }

        ElasticClient::indices()
            ->putMapping($payload->get());

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->searchableAs()
        ));
    }
}
