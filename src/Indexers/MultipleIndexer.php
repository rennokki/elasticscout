<?php

namespace Rennokki\ElasticScout\Indexers;

use Illuminate\Database\Eloquent\Collection;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Migratable;
use Rennokki\ElasticScout\Payloads\RawPayload;
use Rennokki\ElasticScout\Payloads\TypePayload;

class MultipleIndexer implements Indexer
{
    /**
     * {@inheritdoc}
     */
    public function update(Collection $models)
    {
        $model = $models->first();
        $index = $model->getIndex();

        $payload = new TypePayload($model);

        if (in_array(Migratable::class, class_uses_recursive($index))) {
            $payload->useAlias('write');
        }

        if ($documentRefresh = config('elasticscout.refresh_document_on_save')) {
            $payload->set('refresh', $documentRefresh);
        }

        $models->each(function ($model) use ($payload) {
            if ($model::usesSoftDelete() && config('scout.soft_delete', false)) {
                $model->pushSoftDeleteMetadata();
            }

            $modelData = array_merge(
                $model->toSearchableArray(),
                $model->scoutMetadata()
            );

            if (empty($modelData)) {
                return true;
            }

            $actionPayload = (new RawPayload())
                ->set('index._id', $model->getScoutKey());

            $payload
                ->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        ElasticClient::bulk($payload->get());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Collection $models)
    {
        $model = $models->first();

        $payload = new TypePayload($model);

        $models->each(function ($model) use ($payload) {
            $actionPayload = (new RawPayload())
                ->set('delete._id', $model->getScoutKey());

            $payload->add('body', $actionPayload->get());
        });

        $payload->set('client.ignore', 404);

        ElasticClient::bulk($payload->get());
    }
}
