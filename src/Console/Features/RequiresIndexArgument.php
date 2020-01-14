<?php

namespace Rennokki\ElasticScout\Console\Features;

use InvalidArgumentException;
use Rennokki\ElasticScout\Index;
use Symfony\Component\Console\Input\InputArgument;

trait RequiresIndexArgument
{
    /**
     * Get the index.
     *
     * @return \Rennokki\ElasticScout\Index
     */
    protected function getIndex()
    {
        $indexClass = trim($this->argument('index'));
        $indexInstance = new $indexClass;

        if (! $indexInstance instanceof Index) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $indexClass,
                Index::class
            ));
        }

        return new $indexClass;
    }

    /**
     * Get the arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'index',
                InputArgument::REQUIRED,
                'The index class',
            ],
        ];
    }
}
