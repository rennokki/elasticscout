<?php

return [
    /**
     * Define your Elasticsearch connection here.
     */
    'connection' => [
        'hosts' => [
            [
                'host' => env('SCOUT_ELASTICSEARCH_HOST', '127.0.0.1'),
                'port' => env('SCOUT_ELASTICSEARCH_PORT', 9200),
                'scheme' => env('SCOUT_ELASTICSEARCH_SCHEME', null),
                'user' => env('SCOUT_ELASTICSEARCH_USER', null),
                'pass' => env('SCOUT_ELASTICSEARCH_PASSWORD', null),

                'aws_enable' => env('ELASTICSCOUT_AWS_ENABLED', false),
                'aws_region' => env('ELASTICSCOUT_AWS_REGION', 'us-east-1'),
                'aws_key' => env('AWS_ACCESS_KEY_ID', ''),
                'aws_secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            ],
        ],
    ],

    /**
     * Choose the method of indexing.
     *
     * simple: the indexing takes place one-by-one
     * multiple: the indexing is done in bulks
     */
    'indexer' => env('SCOUT_ELASTICSEARCH_INDEXER', 'simple'),

    /**
     * Each time a document is created, updated or deleted, update the mapping
     * attached to the index of the model.
     */
    'update_mapping_on_save' => env('SCOUT_ELASTICSEARCH_UPDATE_MAPPING_ON_SAVE', true),

    /**
     * Elasticsearch "caches" some of the documents and reveals them
     * when it thinks it's the best to. Enabling it will lead to a lot of
     * refreshes that can slow the performance on production.
     *
     * Recommended for production: false
     * Recommended for local/testing: true
     */
    'refresh_document_on_save' => env('SCOUT_ELASTICSEARCH_REFRESH_ON_SAVE', false),
];
