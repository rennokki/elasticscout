<?php

namespace Rennokki\ElasticScout\Tests;

use Rennokki\ElasticScout\Tests\Models\Post;
use Rennokki\ElasticScout\Tests\Models\Restaurant;

class IndexTest extends TestCase
{
    public function test_create_index()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }

    public function test_create_only_alias_index()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->createAlias());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }

    public function test_delete_index()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());

        $this->assertTrue($index->delete());

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());
    }

    public function test_sync_on_new_index()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());

        $this->assertTrue($index->sync());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }

    public function test_sync_without_existence()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->sync());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }

    public function test_sync_mapping_without_mapping()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->syncMapping());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }

    public function test_sync_mapping_with_mapping()
    {
        $restaurant = factory(Restaurant::class)->create();
        $index = $restaurant->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->hasAlias());

        $this->assertTrue($index->syncMapping());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->hasAlias());
    }
}
