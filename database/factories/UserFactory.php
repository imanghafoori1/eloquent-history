<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Imanghafoori\EloquentHistory\Tests\Stubs\Models\User;

$factory->define(User::class, function (Faker $faker, $parameters) {
    return [
        'username' => $faker->unique()->userName,
        'password' => $parameters['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password,
        'email' => $faker->unique()->safeEmail,
        'age' => $faker->randomNumber(2)
    ];
});
