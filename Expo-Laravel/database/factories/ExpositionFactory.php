<?php

namespace Database\Factories;

use App\Models\Exposition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Exposition>
 */
class ExpositionFactory extends Factory
{
    protected $model = Exposition::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'cover_image_path' => null,
            'is_public' => fake()->boolean(80),
            'preset_theme' => -1,
            'player_spawn' => null,
            'floor_texture' => null,
            'ceiling_texture' => null,
            'wall_texture' => null,
            'ambient_track' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(fn () => [
            'is_public' => false,
        ]);
    }
}
