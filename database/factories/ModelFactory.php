<?php

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
 */

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
	$now = Carbon::now();
	return [
		'username' => $faker->username,
		'is_online' => true,
		'last_online' => $now,
	];
});

$factory->define(App\Models\Message::class, function (Faker\Generator $faker) {
	$now = Carbon::now();
	return [
		'message' => $faker->realText(),
	];
});
