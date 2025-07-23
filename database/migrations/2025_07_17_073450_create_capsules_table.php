<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('capsules', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('title');
      $table->text('message');
      $table->string('cover_image_url')->nullable();
      $table->string('emoji')->default('😐');
      $table->string('color')->default('#ffffff');
      $table->string('mood')->default('neutral');
      $table->enum('privacy', ['private', 'public', 'unlisted']);
      $table->string('share_token')->nullable()->unique();
      $table->decimal('lat', 10, 7)->nullable();
      $table->decimal('long', 10, 7)->nullable();
      $table->string('ip_address')->nullable();
      $table->string('country')->nullable();
      $table->string('city')->nullable();
      $table->dateTime('reveal_at');
      $table->boolean('surprise')->default(false);
      $table->boolean('email_sent')->default(false);

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('capsules');
  }
};
