<?php

namespace Rennokki\ElasticScout\Payloads\Features;

trait HasProtectedKeys
{
    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (in_array($key, $this->protectedKeys)) {
            return $this;
        }

        return parent::set($key, $value);
    }
}
