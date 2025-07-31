<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Capsule>
 */
class CapsuleFactory extends Factory {
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array {
    $privacyOptions = ['private', 'public', 'unlisted'];
    $emojis = ['😐', '😊', '😎', '😢', '🤔', '😡', '😍', '😴', '🤩'];

    return [
      'user_id' => \App\Models\User::factory(),
      'title' => $this->faker->sentence(4, true),
      'message' => $this->faker->paragraphs(2, true),
      'cover_image_url' => null,
      'emoji' => $this->faker->randomElement($emojis),
      'color' => $this->faker->hexColor(),
      'mood' => $this->faker->word(),
      'privacy' => $this->faker->randomElement($privacyOptions),
      'share_token' => $this->faker->unique()->uuid(),
      'lat' => $this->faker->latitude(),
      'long' => $this->faker->longitude(),
      'ip_address' => $this->faker->ipv4(),
      'country' => $this->faker->country(),
      'city' => $this->faker->city(),
      'reveal_at' => $this->faker->dateTimeBetween('+1 days', '+1 year'),
      'surprise' => $this->faker->boolean(20),
      'email_sent' => false,
    ];
  }

  /**
   * Attach tags after creating a capsule
   */
  public function configure() {
    return $this->afterCreating(function ($capsule) {
      $tagIds = Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id');
      $capsule->tags()->attach($tagIds);
    });
  }
}
