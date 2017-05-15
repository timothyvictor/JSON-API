<?php

use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

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
$factory->define(Category::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'title' => $faker->sentence(3),
    ];
});

$factory->define(Article::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'title' => $faker->sentence(3),
    ];
});

$factory->define(Author::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'surname' => $faker->lastName
    ];
});

$factory->define(Comment::class, function (Faker\Generator $faker){
    return [
        'body' => $faker->sentence(3),
    ];
});

// $factory->define(App\Concert::class, function (Faker\Generator $faker) {
//     return [
//         'title' => 'Example Band',
//         'subtitle' => 'with the Fakers',
//         'date' => Carbon\Carbon::parse('+2 weeks'),
//         'ticket_price' => 2000,
//         'venue' => 'Example Venue',
//         'venue_address' => '123 Example Lane', 
//         'city' => 'Laraville',
//         'state' => 'ON',
//         'zip' => '17916',
//         'additional_information' => 'Some sample additional information.'
//     ];
// });

// $factory->state(App\Concert::class, 'published', function ($faker) {
//     return [
//         'published_at' => Carbon\Carbon::parse('-1 weeks'),
//     ];
// });

// $factory->state(App\Concert::class, 'unpublished', function ($faker) {
//     return [
//         'published_at' => null,
//     ];
// });