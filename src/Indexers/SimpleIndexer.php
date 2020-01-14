<?php

namespace Rennokki\ElasticScout\Indexers;

use Illuminate\Database\Eloquent\Collection;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Migratable;
use Rennokki\ElasticScout\Payloads\DocumentPayload;

class SimpleIndexer implements Indexer
{
    /**
     * {@inheritdoc}
     */
    public function update(Collection $models)
    {
        $models->each(function ($model) {
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

            $index = $model->getIndex();

            $payload = (new DocumentPayload($model))
                ->set('body', $modelData);

            if (in_array(Migratable::class, class_uses_recursive($index))) {
                $payload->useAlias('write');
            }

            if ($documentRefresh = config('elasticscout.refresh_document_on_save')) {
                $payload->set('refresh', $documentRefresh);
            }

            ElasticClient::index($payload->get());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Collection $models)
    {
        $models->each(function ($model) {
            $payload = (new DocumentPayload($model))
                ->set('client.ignore', 404)
                ->get();

            ElasticClient::delete($payload);
        });
    }
}
