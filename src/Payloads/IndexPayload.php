<?php

namespace Rennokki\ElasticScout\Payloads;

use Exception;
use Rennokki\ElasticScout\Index;
use Rennokki\ElasticScout\Payloads\Features\HasProtectedKeys;

class IndexPayload extends RawPayload
{
    use HasProtectedKeys;

    /**
     * The protected keys.
     *
     * @var array
     */
    protected $protectedKeys = [
        'index',
    ];

    /**
     * The index.
     *
     * @var \Rennokki\ElasticScout\Index
     */
    protected $index;

    /**
     * IndexPayload constructor.
     *
     * @param \Rennokki\ElasticScout\Index $index
     * @return void
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->payload['index'] = $index->getName();
    }

    /**
     * Use an alias.
     *
     * @param  string  $alias
     * @return $this
     * @throws \Exception
     */
    public function useAlias($alias)
    {
        $aliasGetter = 'get'.ucfirst($alias).'Alias';

        if (! method_exists($this->index, $aliasGetter)) {
            throw new Exception(sprintf(
                'The index %s doesn\'t have getter for the %s alias.',
                get_class($this->index),
                $alias
            ));
        }

        $this->payload['index'] = call_user_func([$this->index, $aliasGetter]);

        return $this;
    }
}
