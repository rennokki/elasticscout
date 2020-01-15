<?php

namespace Rennokki\ElasticScout;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Rennokki\ElasticScout\Builders\SearchQueryBuilder;
use Rennokki\ElasticScout\Facades\ElasticClient;
use Rennokki\ElasticScout\Indexers\Indexer;
use Rennokki\ElasticScout\Payloads\TypePayload;
use stdClass;

class ElasticScoutEngine extends Engine
{
    /**
     * The indexer interface.
     *
     * @var \Rennokki\ElasticScout\Indexers\Indexer
     */
    protected $indexer;

    /**
     * Should the mapping be updated.
     *
     * @var bool
     */
    protected $updateMapping;

    /**
     * The updated mappings.
     *
     * @var array
     */
    protected static $updatedMappings = [];

    /**
     * ElasticScoutEngine constructor.
     *
     * @param  \Rennokki\ElasticScout\Indexers\Indexer  $indexer
     * @param  bool  $updateMapping
     * @return void
     */
    public function __construct(Indexer $indexer, $updateMapping)
    {
        $this->indexer = $indexer;
        $this->updateMapping = $updateMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function update($models)
    {
        if ($this->updateMapping) {
            $self = $this;

            $models->each(function ($model) use ($self) {
                $modelClass = get_class($model);

                if (in_array($modelClass, $self::$updatedMappings)) {
                    return true;
                }

                Artisan::call(
                    'elasticscout:index:sync',
                    ['model' => $modelClass]
                );

                $self::$updatedMappings[] = $modelClass;
            });
        }

        $this
            ->indexer
            ->update($models);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($models)
    {
        $this
            ->indexer
            ->delete($models);
    }

    /**
     * Build the payload collection.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return \Illuminate\Support\Collection
     */
    public function buildSearchQueryPayloadCollection(Builder $builder, array $options = [])
    {
        if ($builder instanceof SearchQueryBuilder) {
            $payloadCollection =
                $this->initializeSearchQueryPayloadBuilder(
                    $builder, $options
                );
        } else {
            $payloadCollection =
                $this->initializeElasticsearchPayloadBuilder(
                    $builder, $options
                );
        }

        return $payloadCollection->map(function (TypePayload $payload) use ($builder, $options) {
            $payload
                ->setIfNotEmpty('body._source', $builder->select)
                ->setIfNotEmpty('body.collapse.field', $builder->collapse)
                ->setIfNotEmpty('body.sort', $builder->orders)
                ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
                ->setIfNotEmpty('body.profile', $options['profile'] ?? null)
                ->setIfNotNull('body.from', $builder->offset)
                ->setIfNotNull('body.size', $builder->limit);

            foreach ($builder->wheres as $clause => $filters) {
                switch ($clause) {
                    case 'must':
                    case 'must_not':
                    case 'should':
                    case 'filter':
                        $clauseKey = "body.query.bool.filter.bool.{$clause}";

                        $clauseValue = array_merge(
                            $payload->get($clauseKey, []),
                            $filters
                        );

                        $payload->setIfNotEmpty(
                            $clauseKey, $clauseValue
                        );
                        break;

                    case 'body_appends':
                        $clauseKey = 'body';

                        foreach ($filters as $field => $value) {
                            $payload->add(
                                "{$clauseKey}.{$field}",
                                $value,
                                false
                            );
                        }
                        break;

                    case 'query_appends':
                        $clauseKey = 'body.query';

                        foreach ($filters as $field => $value) {
                            $payload->add(
                                "{$clauseKey}.{$field}",
                                $value,
                                false
                            );
                        }
                        break;
                }
            }

            return $payload->get();
        });
    }

    /**
     * Build the payload for the custom query builder.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return \Illuminate\Support\Collection
     */
    protected function initializeElasticsearchPayloadBuilder(Builder $builder, array $options = [])
    {
        $payloadCollection = collect();

        $payload = (new TypePayload($builder->model))
                ->setIfNotEmpty(
                    'body.query.bool.must.match_all',
                    new stdClass()
                );

        $payloadCollection->push($payload);

        return $payloadCollection;
    }

    /**
     * Build the payload for the search query builder.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return \Illuminate\Support\Collection
     */
    protected function initializeSearchQueryPayloadBuilder(Builder $builder, array $options = [])
    {
        $payloadCollection = collect();
        $searchRules = $builder->rules ?: $builder->model->getSearchRules();

        foreach ($searchRules as $rule) {
            $payload = new TypePayload($builder->model);

            if (is_callable($rule)) {
                $payload->setIfNotEmpty('body.query.bool', call_user_func($rule, $builder));
            } else {
                if ($rule->isApplicable()) {
                    $payload->setIfNotEmpty('body.query.bool', $rule->buildQueryPayload($builder));

                    if ($options['highlight'] ?? true) {
                        $payload->setIfNotEmpty('body.highlight', $rule->buildHighlightPayload($builder));
                    }
                } else {
                    continue;
                }
            }

            $payloadCollection->push($payload);
        }

        return $payloadCollection;
    }

    /**
     * Perform the search.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return array|mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                ElasticClient::getFacadeRoot(),
                $builder->query,
                $options
            );
        }

        $results = [];

        $this
            ->buildSearchQueryPayloadCollection($builder, $options)
            ->each(function ($payload) use (&$results) {
                $results = ElasticClient::search($payload);

                $results['_payload'] = $payload;

                if ($this->getTotalCount($results) > 0) {
                    return false;
                }
            });

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $builder
            ->from(($page - 1) * $perPage)
            ->take($perPage);

        return $this->performSearch($builder);
    }

    /**
     * Explain the search.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return array|mixed
     */
    public function explain(Builder $builder)
    {
        return $this->performSearch($builder, [
            'explain' => true,
        ]);
    }

    /**
     * Profile the search.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return array|mixed
     */
    public function profile(Builder $builder)
    {
        return $this->performSearch($builder, [
            'profile' => true,
        ]);
    }

    /**
     * Return the number of documents found.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return int
     */
    public function count(Builder $builder)
    {
        $count = 0;

        $this
            ->buildSearchQueryPayloadCollection($builder, ['highlight' => false])
            ->each(function ($payload) use (&$count) {
                $result = ElasticClient::count($payload);

                $count = $result['count'] ?? 0;

                if ($count > 0) {
                    return false;
                }
            });

        return $count;
    }

    /**
     * Make a raw search.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $query
     * @return mixed
     */
    public function searchRaw(Model $model, $query)
    {
        $payload = (new TypePayload($model))
            ->setIfNotEmpty('body', $query)
            ->get();

        return ElasticClient::search($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id');
    }

    /**
     * {@inheritdoc}
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) == 0) {
            return Collection::make();
        }

        $scoutKeyName = $model->getScoutKeyName();

        $columns = Arr::get($results, '_payload.body._source');

        if (is_null($columns)) {
            $columns = ['*'];
        } else {
            $columns[] = $scoutKeyName;
        }

        $ids = $this->mapIds($results)->all();

        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $models = $query
            ->whereIn($scoutKeyName, $ids)
            ->get($columns)
            ->keyBy($scoutKeyName);

        return Collection::make($results['hits']['hits'])
            ->map(function ($hit) use ($models) {
                $id = $hit['_id'];

                if (isset($models[$id])) {
                    $model = $models[$id];

                    if (isset($hit['highlight'])) {
                        $model->highlight = new Highlight($hit['highlight']);
                    }

                    return $model;
                }
            })
            ->filter()
            ->values();
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function flush($model)
    {
        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $query
            ->orderBy($model->getScoutKeyName())
            ->unsearchable();
    }
}
