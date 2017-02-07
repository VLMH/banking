<?php

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return ['hkid' => $faker->text($maxNbChars = 5) ];
});
