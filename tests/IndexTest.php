<?php

namespace Rennokki\ElasticScout\Tests;

use Rennokki\ElasticScout\Tests\Models\Post;
use Rennokki\ElasticScout\Tests\Models\Restaurant;

class IndexTest extends TestCase
{
    public function test_create_index()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());
    }

    public function test_create_only_alias_index()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertTrue($index->createAlias());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());
    }

    public function test_delete_index()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());

        $this->assertTrue($index->delete());

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());
    }

    public function test_sync_on_new_index()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertTrue($index->create());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());

        $this->assertTrue($index->sync());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());
    }

    public function test_sync_without_existence()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertTrue($index->sync());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());
    }

    public function test_sync_mapping_without_mapping()
    {
        $post = factory(Post::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertFalse($index->syncMapping());

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());
    }

    public function test_sync_mapping_with_mapping()
    {
        $post = factory(Restaurant::class)->create();
        $index = $post->getIndex();

        $this->assertFalse($index->exists());
        $this->assertFalse($index->aliasExists());

        $this->assertTrue($index->syncMapping());

        $this->assertTrue($index->exists());
        $this->assertTrue($index->aliasExists());
    }
}
