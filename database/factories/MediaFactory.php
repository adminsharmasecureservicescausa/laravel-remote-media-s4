<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => fake()->paragraph(3),
            's3_key' => 'videos/secure/' . Str::uuid() . '.mp4',
            'mime_type' => 'video/mp4',
            'duration' => fake()->numberBetween(60, 7200), // 1 min to 2 hours
            'file_size' => fake()->numberBetween(10485760, 1073741824), // 10MB to 1GB
            'thumbnail_url' => null,
            'is_active' => true,
        ];
    }

    /**
     * State for HLS video stream format.
     */
    public function hls(): static
    {
        return $this->state(fn (array $attributes) => [
            's3_key' => 'videos/hls/' . Str::uuid() . '/master.m3u8',
            'mime_type' => 'application/x-mpegURL',
        ]);
    }

    /**
     * State for inactive media.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
