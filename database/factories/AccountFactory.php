<?php

$factory->define(App\Account::class, function (Faker\Generator $faker) {
    return [
        'user_id' => 1,
        'balance' => 10000, // 100.00
    ];
});
