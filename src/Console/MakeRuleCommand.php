<?php

namespace Rennokki\ElasticScout\Console;

use Illuminate\Console\GeneratorCommand;

class MakeRuleCommand extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'make:elasticscout:rule';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new search rule.';

    /**
     * {@inheritdoc}
     */
    protected $type = 'rule';

    /**
     * {@inheritdoc}
     */
    public function getStub()
    {
        return __DIR__.'/stubs/rule.stub';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\SearchRules';
    }
}
