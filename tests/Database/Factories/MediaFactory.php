<?php

namespace Turahe\Media\Tests\Database\Factories;

use Turahe\Media\Tests\Models\Media;

class MediaFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'hash' => $this->faker->sha1,
            'name' => $this->faker->name,
            'file_name' => $this->faker->name,
            'disk' => $this->faker->randomElement(['local', 'public']),
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(1, 100),
            'custom_attribute' => $this->faker->name,
        ];
    }
}
