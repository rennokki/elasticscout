<?php

namespace Rennokki\ElasticScout\Tests\Builders;

use Rennokki\ElasticScout\Builders\ElasticsearchBuilder;
use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Model;

class ElasticsearchBuilderTest extends AbstractTestCase
{
    use Model;

    public function testCreationWithSoftDelete()
    {
        $builder = new ElasticsearchBuilder($this->mockModel(), null, true);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            '__soft_deleted' => 0,
                        ],
                    ],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testCreationWithoutSoftDelete()
    {
        $builder = new ElasticsearchBuilder($this->mockModel(), null, false);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereEq()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', 0)
            ->where('bar', '=', 1);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['foo' => 0]],
                    ['term' => ['bar' => 1]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testMust()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->must(['term' => ['tag' => 'wow']]);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['tag' => 'wow']],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testMustNot()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->mustNot(['term' => ['tag' => 'wow']]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['term' => ['tag' => 'wow']],
                ],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testShould()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->should(['term' => ['tag' => 'wow']]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
                'should' => [
                    ['term' => ['tag' => 'wow']],
                ],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testFilter()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->filter(['term' => ['tag' => 'wow']]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
                'should' => [],
                'filter' => [
                    ['term' => ['tag' => 'wow']],
                ],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testAppendsToBody()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->appendToBody('minimum_should_match', 1);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [
                    'minimum_should_match' => 1,
                ],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testAppendsToQuery()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->appendToQuery('minimum_should_match', 1);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [
                    'minimum_should_match' => 1,
                ],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotEq()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', '!=', 'bar');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['term' => ['foo' => 'bar']],
                ],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGt()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', '>', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gt' => 0]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGte()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereLt()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', '<', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['lt' => 0]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereLte()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereIn()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [
                    ['terms' => ['foo' => [0, 1]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotIn()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereNotIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['terms' => ['foo' => [0, 1]]],
                ],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereBetween()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotBetween()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereNotBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereExists()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereExists('foo');

        $this->assertEquals(
            [
                'must' => [
                    ['exists' => ['field' => 'foo']],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotExists()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereNotExists('foo');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['exists' => ['field' => 'foo']],
                ],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereRegexp()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereRegexp('foo', '.*')
            ->whereRegexp('bar', '^test.*', 'EMPTY|NONE');

        $this->assertEquals(
            [
                'must' => [
                    ['regexp' => ['foo' => ['value' => '.*', 'flags' => 'ALL']]],
                    ['regexp' => ['bar' => ['value' => '^test.*', 'flags' => 'EMPTY|NONE']]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhen()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->when(
                false,
                function (ElasticsearchBuilder $builder) {
                    return $builder->where('case0', 0);
                }
            )
            ->when(
                false,
                function (ElasticsearchBuilder $builder) {
                    return $builder->where('case1', 1);
                },
                function (ElasticsearchBuilder $builder) {
                    return $builder->where('case2', 2);
                }
            )
            ->when(
                true,
                function (ElasticsearchBuilder $builder) {
                    return $builder->where('case3', 3);
                }
            );

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['case2' => 2]],
                    ['term' => ['case3' => 3]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoDistance()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereGeoDistance('foo', [-20, 30], '10m');

        $this->assertEquals(
            [
                'must' => [
                    ['geo_distance' => ['distance' => '10m', 'foo' => [-20, 30]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoBoundingBox()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereGeoBoundingBox('foo', ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_bounding_box' => ['foo' => ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoPolygon()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereGeoPolygon('foo', [[-70, 40], [-80, 30], [-90, 20]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_polygon' => ['foo' => ['points' => [[-70, 40], [-80, 30], [-90, 20]]]]],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoShape()
    {
        $shape = [
            'type' => 'circle',
            'radius' => '1km',
            'coordinates' => [
                4.89994,
                52.37815,
            ],
        ];

        $relation = 'WITHIN';

        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->whereGeoShape('foo', $shape, $relation);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'geo_shape' => [
                            'foo' => [
                                'shape' => $shape,
                                'relation' => $relation,
                            ],
                        ],
                    ],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testOrderBy()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->orderBy('foo')
            ->orderBy('bar', 'DESC');

        $this->assertEquals(
            [
                ['foo' => 'asc'],
                ['bar' => 'desc'],
            ],
            $builder->orders
        );
    }

    public function testWith()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->with('RelatedModel');

        $this->assertEquals(
            'RelatedModel',
            $builder->with
        );
    }

    public function testFrom()
    {
        $builder = new ElasticsearchBuilder($this->mockModel());

        $this->assertEquals(
            0,
            $builder->offset
        );

        $builder->from(100);

        $this->assertEquals(
            100,
            $builder->offset
        );
    }

    public function testCollapse()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->collapse('foo');

        $this->assertEquals(
            'foo',
            $builder->collapse
        );
    }

    public function testSelect()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel()))
            ->select(['foo', 'bar']);

        $this->assertEquals(
            ['foo', 'bar'],
            $builder->select
        );
    }

    public function testWithTrashed()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel(), null, true))
            ->withTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }

    public function testOnlyTrashed()
    {
        $builder = (new ElasticsearchBuilder($this->mockModel(), null, true))
            ->onlyTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            '__soft_deleted' => 1,
                        ],
                    ],
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
                'should' => [],
                'filter' => [],
                'body_appends' => [],
                'query_appends' => [],
            ],
            $builder->wheres
        );
    }
}
