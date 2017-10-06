<?php

use Faker\Generator as Faker;

$factory->define(App\Content::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'start_date' => $faker->date('Y-m-d', 'now'),
        'end_date' => $faker->date('Y-m-d'),
        'size' => $faker->numberBetween(100,1000),
        'weight' => $faker->numberBetween(100,1000),
    ];
});
