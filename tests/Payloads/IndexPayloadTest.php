<?php

namespace Rennokki\ElasticScout\Tests\Payloads;

use Rennokki\ElasticScout\Payloads\IndexPayload;
use Rennokki\ElasticScout\Tests\AbstractTestCase;
use Rennokki\ElasticScout\Tests\Dependencies\Index;

class IndexPayloadTest extends AbstractTestCase
{
    use Index;

    public function testDefault()
    {
        $index = $this->mockIndex();
        $payload = new IndexPayload($index);

        $this->assertEquals(
            ['index' => 'test'],
            $payload->get()
        );
    }

    public function testUseAlias()
    {
        $index = $this->mockIndex([
            'name' => 'foo',
        ]);

        $payload = (new IndexPayload($index))
            ->useAlias('write');

        $this->assertEquals(
            ['index' => 'foo_write'],
            $payload->get()
        );
    }

    public function testSet()
    {
        $index = $this->mockIndex([
            'name' => 'foo',
        ]);

        $payload = (new IndexPayload($index))
            ->set('index', 'bar')
            ->set('settings', ['key' => 'value']);

        $this->assertEquals(
            [
                'index' => 'foo',
                'settings' => ['key' => 'value'],
            ],
            $payload->get()
        );
    }
}
