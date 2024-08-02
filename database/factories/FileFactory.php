<?php

namespace Database\Factories;

use App\Models\Directory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => $this->faker->word . '.' . $this->faker->fileExtension,
            'directory_id' => Directory::factory(),
            'path'         => '/' . $this->faker->word . '/' . $this->faker->word . '.' . $this->faker->fileExtension,
        ];
    }
}
