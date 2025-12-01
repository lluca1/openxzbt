<?php

namespace Database\Factories;

use App\Models\Exposition;
use App\Models\Tile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Tile>
 */
class TileFactory extends Factory
{
    protected $model = Tile::class;

    public function definition(): array
    {
        return [
            'exposition_id' => Exposition::factory(),
            'tile_identifier' => (string) Str::uuid(),
            'type' => fake()->numberBetween(0, 3),
            'position' => [
                fake()->randomFloat(2, -5, 5),
                0.0,
                fake()->randomFloat(2, -5, 5),
            ],
            'rotation' => [0.0, fake()->randomFloat(2, 0, 360), 0.0],
        ];
    }
}
