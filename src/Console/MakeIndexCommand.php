<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\GeneratorCommand;

class MakeIndexCommand extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'make:elasticscout:index';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new Elasticsearch index.';

    /**
     * {@inheritdoc}
     */
    protected $type = 'index';

    /**
     * {@inheritdoc}
     */
    public function getStub()
    {
        return __DIR__.'/stubs/index.stub';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Indexes';
    }
}
