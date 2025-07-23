<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory {
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array {
    $type = $this->faker->randomElement(['image', 'audio']);

    $filePath = $type === 'image'
      ? $this->faker->imageUrl(640, 480, 'nature', true)
      : $this->faker->randomElement([
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
      ]);

    return [
      'capsule_id' => \App\Models\Capsule::factory(),
      'file_path' => $filePath,
      'type' => $type,
    ];
  }
}
