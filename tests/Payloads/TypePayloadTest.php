<?php

namespace Rennokki\ElasticScout\Tests\Payloads;

use Rennokki\ElasticScout\Payloads\TypePayload;
use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Model;

class TypePayloadTest extends AbstractTestCase
{
    use Model;

    public function testDefault()
    {
        $model = $this->mockModel();
        $payload = new TypePayload($model);

        $this->assertEquals(
            [
                'index' => 'test',
                'type' => 'test',
            ],
            $payload->get()
        );
    }

    public function testSet()
    {
        $index = $this->mockIndex([
            'name' => 'foo',
        ]);

        $model = $this->mockModel([
            'searchable_as' => 'bar',
            'index' => $index,
        ]);

        $payload = (new TypePayload($model))
            ->set('index', 'test_index')
            ->set('type', 'test_type')
            ->set('body', []);

        $this->assertEquals(
            [
                'index' => 'foo',
                'type' => 'bar',
                'body' => [],
            ],
            $payload->get()
        );
    }
}
