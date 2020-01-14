<?php

namespace Rennokki\ElasticScout;

use Exception;
use Laravel\Scout\Searchable as SourceSearchable;
use Rennokki\ElasticScout\Builders\ElasticsearchBuilder;
use Rennokki\ElasticScout\Builders\SearchQueryBuilder;

trait Searchable
{
    use SourceSearchable {
        SourceSearchable::bootSearchable as sourceBootSearchable;
        SourceSearchable::getScoutKeyName as sourceGetScoutKeyName;
    }

    /**
     * The highligths.
     *
     * @var \Rennokki\ElasticScout\Highlight|null
     */
    private $highlight = null;

    /**
     * Defines if the model is searchable.
     *
     * @var bool
     */
    protected static $isSearchableTraitBooted = false;

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootSearchable()
    {
        if (static::$isSearchableTraitBooted) {
            return;
        }

        self::sourceBootSearchable();

        static::$isSearchableTraitBooted = true;
    }

    /**
     * Get the index.
     *
     * @return \Rennokki\ElasticScout\Index
     * @throws \Exception
     */
    public function getIndex()
    {
        if (! $this->elasticScoutIndex) {
            throw new Exception(sprintf(
                'The model %s has no index set.',
                __CLASS__
            ));
        }

        return new $this->elasticScoutIndex;
    }

    /**
     * Get the search rules.
     *
     * @return array
     */
    public function getSearchRules()
    {
        return isset($this->searchRules) && count($this->searchRules) > 0 ?
            $this->searchRules : [SearchRule::class];
    }

    /**
     * Execute the search.
     *
     * @param  string  $query
     * @param  callable|null  $callback
     * @return \Rennokki\ElasticScout\Builders\SearchQueryBuilder
     */
    public static function search(string $query, $callback = null)
    {
        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        return new SearchQueryBuilder(new static, $query, $callback, $softDelete);
    }

    /**
     * Enable the communication with the ES Client.
     *
     * @param  callable|null  $callback
     * @return \Rennokki\ElasticScout\Builders\ElasticsearchBuilder
     */
    public static function elasticsearch($callback = null)
    {
        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        return new ElasticsearchBuilder(new static, $callback, $softDelete);
    }

    /**
     * Execute a raw search.
     *
     * @param  array  $query
     * @return array
     */
    public static function searchRaw(array $query)
    {
        $model = new static();

        return $model
            ->searchableUsing()
            ->searchRaw($model, $query);
    }

    /**
     * Set the highlight attribute.
     *
     * @param  \Rennokki\ElasticScout\Highlight $value
     * @return void
     */
    public function setHighlightAttribute(Highlight $value)
    {
        $this->highlight = $value;
    }

    /**
     * Get the highlight attribute.
     *
     * @return \Rennokki\ElasticScout\Highlight|null
     */
    public function getHighlightAttribute()
    {
        return $this->highlight;
    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getScoutKeyName()
    {
        return $this->getKeyName();
    }
}
