<?php

namespace Rennokki\ElasticScout\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Rennokki\ElasticScout\Searchable;

class TypePayload extends IndexPayload
{
    /**
     * The model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * TypePayload constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     * @throws \Exception
     */
    public function __construct(Model $model)
    {
        if (! in_array(Searchable::class, class_uses_recursive($model))) {
            throw new Exception(sprintf(
                'The %s model must use the %s trait.',
                get_class($model),
                Searchable::class
            ));
        }

        parent::__construct($model->getIndex());

        $this->model = $model;
        $this->payload['type'] = $model->searchableAs();
        $this->protectedKeys[] = 'type';
    }
}
