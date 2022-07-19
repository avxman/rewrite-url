<?php

namespace Database\Factories;

use Avxman\Rewrite\Models\RouteGroups;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RouteGroupsFactory extends Factory
{
    protected $middleware = [
        "[]",
        "['web']",
        "['api']",
        "['web', 'api']",
        "[]",
        "[]"
    ];
    protected $locale = [
        '',
        'ua',
        'ru',
        'en'
    ];

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RouteGroups::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'enable'=>$this->faker->randomElement([1,1,0,1,0,1,0,1,1,0]),
            'position'=>0,
            'group'=>$this->faker->unique()->firstName,
            'prefix'=>$this->faker->randomElement($this->locale),
            'middleware'=>$this->faker->randomElement($this->middleware)
        ];
    }
}
