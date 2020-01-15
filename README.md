ElasticScout Driver for Elasticsearch 7.1+
================
This package is an initial fork from [the original package by Babenko Ivan's Elasticsearch](https://github.com/babenkoivan/scout-elasticsearch-driver). This package intends to have separate branches for each ES version and keep updating them individually to maintain the LTS better.

* [Install](#install)
* [Base Usage](#base-usage)
* [Indexes](#indexes)
* [Search Query](#search-query)
* [Filter Query](#filter-query)
* [Rules](#rules)
* [Debugging](#debugging)

Install
------
Install the package using Composer CLI:

```bash
$ composer require rennokki/elasticscout
```

If your Laravel package does not support auto-discovery, add this to your `config/app.php` file:

```php
'providers' => [
    ...
    Rennokki\ElasticScout\ElasticScoutServiceProvider::class,
    ...
];
```

Publish the config files:

```bash
$ php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
$ php artisan vendor:publish --provider="Rennokki\ElasticScout\ElasticScoutServiceProvider"
```

Configuring Scout
-----
In your `.env` file, set yout `SCOUT_DRIVER` to `elasticscout`, alongside with Elasticsearch configuration:

```env
SCOUT_DRIVER=elasticscout

SCOUT_ELASTICSEARCH_HOST=localhost
SCOUT_ELASTICSEARCH_PORT=9200
```

Indexes
-----
### Creating an index
In Elasticsearch, the Index is the equivalent of a table in MySQL, or a collection in MongoDB. You can create an index class using artisan:

```bash
$ php artisan make:elasticscout:index PostIndex
```

You will have something like this in `app/Indexes/PostIndex.php`:

```php
<?php

namespace App\Indexes;

use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Migratable;

class PostIndex extends Index
{
    use Migratable;

    /**
     * The settings applied to this index.
     *
     * @var array
     */
    protected $settings = [
        //
    ];

    /**
     * The mapping for this index.
     *
     * @var array
     */
    protected $mapping = [
        //
    ];
}
```

The key here is that you can set settings and a mapping for each index.
You can find more on Elasticsearch's documentation website about [mappings](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html#_explicit_mappings) and [settings](https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html).

Here's an example on creating a mapping for a field that is [a geo-point datatype](https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-point.html):

```php
class RestaurantIndex extends Index
{
    ...
    protected $mapping = [
        'properties' => [
            'location' => [
                'type' => 'geo_point',
            ],
        ],
    ];
}
```

Here is an example on creating a new analyzer in the `$settings` variable for [a whitespace tokenizer](https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html#update-settings-analysis):

```php
class PostIndex extends Index
{
    ...
    protected settings = [
        'analysis' => [
            'analyzer' => [
                'content' => [
                    'type' => 'custom',
                    'tokenizer' => 'whitespace',
                ],
            ],
        ],
    ];
}
```

If you wish to change the name of the index, you can do so by overriding the `$name` variable:

```php
class PostIndex extends Index
{
    protected $name = 'posts_index_2';
}
```

### Attach the index to a model
All the models that can be searched into should use the `Rennokki\ElasticScout\Searchable` trait and implement the `HasElasticScoutIndex` interface:

```php
use Rennokki\ElasticScout\Contracts\HasElasticScoutIndex;
use Rennokki\ElasticScout\Searchable;

class Post extends Model implements HasElasticScoutIndex
{
    use Searchable;
}
```

Additionally, the model should also specify the index class:

```php
use App\Indexes\PostIndex;
use Rennokki\ElasticScout\Contracts\HasElasticScoutIndex;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Searchable;

class Post extends Model implements HasElasticScoutIndex
{
    use Searchable;

    /**
     * Get the index instance class for Elasticsearch.
     *
     * @return \Rennokki\ElasticScout\Index
     */
    public function getElasticScoutIndex(): Index
    {
        return new PostIndex($this);
    }
}
```

### Publish the index to Elasticsearch
To publish the index to Elasticsearch, you should sync the index:

```bash
$ php artisan elasticscout:index:sync App\\Post
```

Now, each time your model creates,updates or deletes new records, they will be automatically synced to Elasticsearch.

**In case you want to import already-existing data, please use the [scout:import command](https://laravel.com/docs/5.8/scout#batch-import) that is described in the Scout documentation.**

Syncing the index can also be done within your code:

```php
$restaurant = Restaurant::first();

$restaurant->getIndex()->sync(); // returns true/false
```

Search Query
-----
To query data into Elasticsearch, you may use the `search()` method:

```php
Post::search('Laravel')
    ->take(30)
    ->from(10)
    ->get();
```

In case you want just the number of the documents, you can do so:

```php
$posts = Post::search('Lumen')->count();
```

Filter Query
-----
ElasticScout allows you to create a custom query using built-in methods by going through the `elasticsearch()` method.

### Must, Must not, Should, Filter
You can use Elasticsearch's must, must_not, should and filter keys directly in the builder.
Keep in mind that you can chain as many as you want.

```php
Post::elasticsearch()
    ->must(['term' => ['tag' => 'wow']])
    ->should(['term' => ['tag' => 'yay']])
    ->shouldNot(['term' => ['tag' => 'nah']])
    ->filter(['term' => ['tag' => 'wow']])
    ->get();
```

### Append to body or query
You can append data to body or query keys.

```php
// apend to the body payload
Post::elasticsearch()
    ->appendToBody('minimum_should_match', 1)
    ->appendToBody('some_field', ['array' => 'yes'])
    ->get();
```

```php
// append to the query payload
Post::elasticsearch()
    ->appendToQuery('some_field', 'value')
    ->appendToQuery('some_other_field', ['array' => 'yes'])
    ->get();
```

#### Wheres

```php
Post::elasticsearch()
    ->where('title.keyword', 'Elasticsearch')
    ->first();
```

```php
Book::elasticsearch()
    ->whereBetween('price', [100, 200])
    ->first();
```

```php
Book::elasticsearch()
    ->whereNotBetween('price', [100, 200])
    ->first();
```

### Regex filters

```php
Post::elasticsearch()
    ->whereRegexp('title.raw', 'A.+')
    ->get();
```

### Existence check
Since Elasticsearch has a NoSQL structure, you should be able to check if a field exists.

```php
Post::elasticsearch()
    ->whereExists('meta')
    ->whereNotExists('new_meta')
    ->get();
```

### Geo-type searches

```php
Restaurant::whereGeoDistance('location', [-70, 40], '1000m')
    ->get();
```

```php
Restaurant::whereGeoBoundingBox(
    'location',
    [
        'top_left' => [-74.1, 40.73],
        'bottom_right' => [-71.12, 40.01],
    ]
)->get();
```

```php
Restaurant::whereGeoPolygon(
    'location',
    [
        [-70, 40], [-80, 30], [-90, 20],
    ]
)->get();
```

```php
Restaurant::whereGeoShape(
    'shape',
    [
        'type' => 'circle',
        'radius' => '1km',
        'coordinates' => [4, 52],
    ],
    'WITHIN'
)->get();
```

Rules
-----
A search rule is a class that can be used on multiple queries, helping you to define custom payload only once.
To create a rule, use the artisan command:

```bash
$ php artisan make:elasticscout:rule NameRule
```

You will get something like this:

```php
<?php

namespace App\SearchRules;

use Rennokki\ElasticScout\Builders\SearchQueryBuilder;
use Rennokki\ElasticScout\SearchRule;

class NameRule extends SearchRule
{
    /**
     * Initialize the rule.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the highlight payload.
     *
     * @param  SearchQueryBuilder  $builder
     * @return array
     */
    public function buildHighlightPayload(SearchQueryBuilder $builder)
    {
        return [
            //
        ];
    }

    /**
     * Build the query payload.
     *
     * @param  SearchQueryBuilder  $builder
     * @return array
     */
    public function buildQueryPayload(SearchQueryBuilder $builder)
    {
        return [
            //
        ];
    }
}
```

### Query Payload
Within the `buildQueryPayload()`, you should define the query payload that will take place during the query.

For example, you can get started with some bool query. Details about the bool query you can find [in the Elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html).

```php
class NameRule extends SearchRule
{
    public function buildQueryPayload(SearchQueryBuilder $builder)
    {
        return [
            'must' => [
                'match' => [
                    // access the search phrase from the $builder
                    'name' => $builder->query,
                ],
            ],
        ];
    }
}
```

To apply the rule, you can call the `->rule()` method at query runtime:

```php
use App\SearchRules\NameRule;

Restaurant::search('Dominos')
    ->rule(new NameRule)
    ->get();
```

### Highlight Payload
When building the highlight payload, you can pass the array to the `buildHighlightPayload()` method.
More details on highlighting can be found [in the Elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#request-body-search-highlighting).

```php
class NameRule extends SearchRule
{
    public function buildHighlightPayload(SearchQueryBuilder $builder)
    {
        return [
            'fields' => [
                'name' => [
                    'type' => 'plain',
                ],
            ],
        ];
    }
}
```

To access the payload, you can use the `$highlight` attribute from the model (or from each model of the final collection).

```php
use App\SearchRules\NameRule;

$restaurant = Restaurant::search('Dominos')->rule(new NameRule)->first();

$name = $restaurant->highlight->name;
$nameAsString = $restaurant->highlight->nameAsString;
```

**In case you need to pass arguments to the rules, you can do so by adding your construct method.**

```php
// app/SearchRules/NameRule.php
class NameRule extends SearchRule
{
    protected $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function buildQueryPayload(SearchQueryBuilder $builder)
    {
        // Override the name from the rule construct.
        $name = $this->name ?: $builder->query;

        return [
            'must' => [
                'match' => [
                    'name' => $name,
                ],
            ],
        ];
    }
}

Restaurant::search('Dominos')
    ->rule(new NameRule('Pizza Hut'))
    ->get();
```

Debugging
-----
You can debug by explaining the query.

```php
Restaurant::search('Dominos')->explain();
```

You can see how the payload looks like by calling `getPayload()`.

```php
Restaurant::search('Dominos')->getPayload();
```
