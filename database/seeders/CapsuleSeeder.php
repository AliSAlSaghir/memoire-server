<?php

namespace Database\Seeders;

use App\Models\Capsule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CapsuleSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    Capsule::factory()
      ->count(20)
      ->create();
  }
}
