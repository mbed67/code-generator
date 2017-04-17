<?php

return [
    'uuid' => $faker->uuid,
    'string' => $faker->text($maxNbChars = 10),
    'email' => $faker->email,
    'int' => $faker->randomNumber(),
    'firstName' => $faker->firstName,
    'infix' => $faker->word,
    'surname' => $faker->lastName,
    'atomDate' => $faker->date('Y-m-d\TH:i:sP')
];
