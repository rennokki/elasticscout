<?php

namespace Rennokki\ElasticScout\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;

class ElasticsearchBuilder extends Builder
{
    /**
     * The condition array.
     *
     * @var array
     */
    public $wheres = [
        'must' => [],
        'must_not' => [],
        'should' => [],
        'filter' => [],
        'body_appends' => [],
        'query_appends' => [],
    ];

    /**
     * The with array.
     *
     * @var array|string
     */
    public $with;

    /**
     * The offset.
     *
     * @var int
     */
    public $offset;

    /**
     * The collapse parameter.
     *
     * @var string
     */
    public $collapse;

    /**
     * The select array.
     *
     * @var array
     */
    public $select = [];

    /**
     * ElasticsearchBuilder constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  callable|null  $callback
     * @param  bool  $softDelete
     * @return void
     */
    public function __construct(Model $model, $callback = null, $softDelete = false)
    {
        $this->model = $model;
        $this->callback = $callback;

        if ($softDelete) {
            $this->must([
                'term' => [
                    '__soft_deleted' => 0,
                ],
            ]);
        }
    }

    /**
     * Allow the user to specify a new element
     * in the `should` key.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return bool
     */
    public function should($value)
    {
        $this->wheres['should'][] = $value;

        return $this;
    }

    /**
     * Allow the user to specify a new element
     * in the `must` key.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return bool
     */
    public function must($value)
    {
        $this->wheres['must'][] = $value;

        return $this;
    }

    /**
     * Allow the user to specify a new element
     * in the `must_not` key.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return bool
     */
    public function mustNot($value)
    {
        $this->wheres['must_not'][] = $value;

        return $this;
    }

    /**
     * Allow the user to specify a new element
     * in the `filter` key.
     *
     * @param  string  $field
     * @param  array  $value
     * @return bool
     */
    public function filter(array $value)
    {
        $this->wheres['filter'][] = $value;

        return $this;
    }

    /**
     * Allow the user to specify a list
     * of data that will be appended to the body.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return $this
     */
    public function appendToBody(string $field, $value)
    {
        $this->wheres['body_appends'][$field] = $value;

        return $this;
    }

    /**
     * Allow the user to specify a list
     * of data that will be appended to query.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return $this
     */
    public function appendToQuery(string $field, $value)
    {
        $this->wheres['query_appends'][$field] = $value;

        return $this;
    }

    /**
     * Add a where condition.
     * Supported operators are =, >, <, >=, <=, <>, !=.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Term query
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return $this
     */
    public function where($field, $value)
    {
        $args = func_get_args();

        if (count($args) === 3) {
            [$field, $operator, $value] = $args;
        } else {
            $operator = '=';
        }

        switch ($operator) {
            case '=':
                $this->must([
                    'term' => [
                        $field => $value,
                    ],
                ]);
                break;

            case '>':
                $this->must([
                    'range' => [
                        $field => [
                            'gt' => $value,
                        ],
                    ],
                ]);
                break;

            case '<':
                $this->must([
                    'range' => [
                        $field => [
                            'lt' => $value,
                        ],
                    ],
                ]);
                break;

            case '>=':
                $this->must([
                    'range' => [
                        $field => [
                            'gte' => $value,
                        ],
                    ],
                ]);
                break;

            case '<=':
                $this->must([
                    'range' => [
                        $field => [
                            'lte' => $value,
                        ],
                    ],
                ]);
                break;

            case '!=':
            case '<>':
                $this->mustNot([
                    'term' => [
                        $field => $value,
                    ],
                ]);
                break;
        }

        return $this;
    }

    /**
     * Add a whereIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array  $value
     * @return $this
     */
    public function whereIn($field, array $value)
    {
        return $this->must([
            'terms' => [
                $field => $value,
            ],
        ]);
    }

    /**
     * Add a whereNotIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array  $value
     * @return $this
     */
    public function whereNotIn($field, array $value)
    {
        return $this->mustNot([
            'terms' => [
                $field => $value,
            ],
        ]);
    }

    /**
     * Add a whereBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param  string  $field
     * @param  array  $value
     * @return $this
     */
    public function whereBetween($field, array $value)
    {
        return $this->must([
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ]);
    }

    /**
     * Add a whereNotBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param  string  $field
     * @param  array  $value
     * @return $this
     */
    public function whereNotBetween($field, array $value)
    {
        return $this->mustNot([
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ]);
    }

    /**
     * Add a whereExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param  string  $field
     * @return $this
     */
    public function whereExists($field)
    {
        return $this->must([
            'exists' => [
                'field' => $field,
            ],
        ]);
    }

    /**
     * Add a whereNotExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param  string  $field
     * @return $this
     */
    public function whereNotExists($field)
    {
        return $this->mustNot([
            'exists' => [
                'field' => $field,
            ],
        ]);
    }

    /**
     * Add a whereRegexp condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html Regexp query
     *
     * @param  string  $field
     * @param  string  $value
     * @param  string  $flags
     * @return $this
     */
    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        return $this->must([
            'regexp' => [
                $field => [
                    'value' => $value,
                    'flags' => $flags,
                ],
            ],
        ]);
    }

    /**
     * Add a whereGeoDistance condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html Geo distance query
     *
     * @param  string  $field
     * @param  string|array  $value
     * @param  int|string  $distance
     * @return $this
     */
    public function whereGeoDistance($field, $value, $distance)
    {
        return $this->must([
            'geo_distance' => [
                'distance' => $distance,
                $field => $value,
            ],
        ]);
    }

    /**
     * Add a whereGeoBoundingBox condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html Geo bounding box query
     *
     * @param  string  $field
     * @param  array  $value
     * @return $this
     */
    public function whereGeoBoundingBox($field, array $value)
    {
        return $this->must([
            'geo_bounding_box' => [
                $field => $value,
            ],
        ]);
    }

    /**
     * Add a whereGeoPolygon condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html Geo polygon query
     *
     * @param  string  $field
     * @param  array  $points
     * @return $this
     */
    public function whereGeoPolygon($field, array $points)
    {
        return $this->must([
            'geo_polygon' => [
                $field => [
                    'points' => $points,
                ],
            ],
        ]);
    }

    /**
     * Add a whereGeoShape condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html Querying Geo Shapes
     *
     * @param  string  $field
     * @param  array  $shape
     * @param  string  $relation
     * @return $this
     */
    public function whereGeoShape($field, array $shape, $relation = 'INTERSECTS')
    {
        return $this->must([
            'geo_shape' => [
                $field => [
                    'shape' => $shape,
                    'relation' => $relation,
                ],
            ],
        ]);
    }

    /**
     * Add a orderBy clause.
     *
     * @param  string  $field
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->orders[] = [
            $field => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Explain the request.
     *
     * @return array
     */
    public function explain()
    {
        return $this
            ->engine()
            ->explain($this);
    }

    /**
     * Profile the request.
     *
     * @return array
     */
    public function profile()
    {
        return $this
            ->engine()
            ->profile($this);
    }

    /**
     * Build the payload.
     *
     * @return array
     */
    public function getPayload()
    {
        return $this
            ->engine()
            ->buildSearchQueryPayloadCollection($this);
    }

    /**
     * Eager load some some relations.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->with = $relations;

        return $this;
    }

    /**
     * Set the query offset.
     *
     * @param  int  $offset
     * @return $this
     */
    public function from($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $collection = parent::get();

        if (isset($this->with) && $collection->count() > 0) {
            $collection->load($this->with);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        $paginator = parent::paginate($perPage, $pageName, $page);

        if (isset($this->with) && $paginator->total() > 0) {
            $paginator
                ->getCollection()
                ->load($this->with);
        }

        return $paginator;
    }

    /**
     * Collapse by a field.
     *
     * @param  string  $field
     * @return $this
     */
    public function collapse(string $field)
    {
        $this->collapse = $field;

        return $this;
    }

    /**
     * Select one or many fields.
     *
     * @param  mixed  $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->select = array_merge(
            $this->select,
            Arr::wrap($fields)
        );

        return $this;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function count()
    {
        return $this
            ->engine()
            ->count($this);
    }

    /**
     * {@inheritdoc}
     */
    public function withTrashed()
    {
        $this->wheres['must'] =
            collect($this->wheres['must'])
                ->filter(function ($item) {
                    return Arr::get($item, 'term.__soft_deleted') !== 0;
                })
                ->values()
                ->all();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onlyTrashed()
    {
        return tap($this->withTrashed(), function () {
            $this->must(['term' => ['__soft_deleted' => 1]]);
        });
    }
}
