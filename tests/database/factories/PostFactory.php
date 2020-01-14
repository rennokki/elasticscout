<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\Rennokki\ElasticScout\Tests\Models\Post::class, function () {
    return [
        'name' => 'Post'.Str::random(5),
        'body' => 'This is the body of the post.',
    ];
});
