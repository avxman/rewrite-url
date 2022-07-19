<?php

namespace Database\Factories;

use Avxman\Rewrite\Models\Routes;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Routes::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'enable'=>$this->faker->randomElement([1,0,1,1,1,0,1,1,0]),
            'name'=>$this->faker->unique()->word(),
            'uri'=>$this->faker->unique()->word(),
        ];
    }
}
