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

$factory->define(\Rennokki\ElasticScout\Tests\Models\Book::class, function () {
    return [
        'name' => 'Book'.Str::random(5),
        'price' => mt_rand(10, 1000),
    ];
});
