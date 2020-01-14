<?php

namespace Rennokki\ElasticScout;

use Illuminate\Support\Str;

abstract class Index
{
    /**
     * The name.
     *
     * @var string
     */
    protected $name;

    /**
     * The settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * The mapping.
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * Get th name.
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->name ?? Str::snake(str_replace('Index', '', class_basename($this)));

        return config('scout.prefix').$name;
    }

    /**
     * Get the settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get the mapping
     *
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }
}
