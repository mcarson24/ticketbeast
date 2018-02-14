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

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$54Tfs2x18n7XpXh1jiv5HeuN0r8K96bV59ttdHs7d7OEuqz/xijXa', // 'secret'
        'remember_token' => str_random(10),
        'stripe_account_id' => 'test_account_1234',
        'stripe_access_token' => 'test_token'

    ];
});

$factory->define(App\Concert::class, function (Faker\Generator $faker) {
    return [
        'title' => 'Example Band',
        'subtitle' => 'with The Fake Openers',
        'additional_information' => 'Some sample additional information.',
        'date' => Carbon::parse('+2 weeks'),
        'venue' => 'The Example Theatre',
        'venue_address' => '123 Example Lane',
        'city' => 'Fakeville',
        'state' => 'ON',
        'zip' => '90210',
        'ticket_price' => 2000,
        'ticket_quantity' => 5,
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        }
    ];
});

$factory->define(App\Ticket::class, function (Faker\Generator $faker) {
    return[
        'concert_id' => function() {
            return factory(App\Concert::class)->create()->id;
        }
    ];
});

$factory->define(App\Order::class, function (Faker\Generator $faker) {
    return[
        'amount'                => 5250,
        'email'                 => 'somebody@example.com',
        'confirmation_number'   => 'ORDERCONFIRMATION1234',
        'card_last_four'        => '1234'
    ];
});

$factory->state(App\Concert::class, 'published', function (Faker\Generator $faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
    ];
});

$factory->state(App\Concert::class, 'unpublished', function (Faker\Generator $faker) {
    return [
        'published_at' => null,
    ];
});

$factory->state(App\Ticket::class, 'reserved', function (Faker\Generator $faker) {
    return [
        'reserved_at' => Carbon::now()
    ];
});

$factory->define(App\Invitation::class, function (Faker\Generator $faker) {
    return [
        'email' => 'someone@example.com',
        'code'  => 'TESTCODE1234'
    ];
});
