<?php

namespace Database\Seeders;

use App\Models\Attachment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    Attachment::factory()->count(20)->create();
  }
}
