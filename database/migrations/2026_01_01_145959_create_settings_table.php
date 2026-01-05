<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('settings', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('key')->unique();
      $table->text('value')->nullable();
      $table->string('type')->default('text'); // text, textarea, image, email, etc
      $table->timestamps();
    });

    DB::table('settings')->insert([
      ['id' => (string) Str::uuid(), 'key' => 'site_name', 'value' => 'Forge Player', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_description', 'value' => 'Video Player & Various Host Sources + Multi Quality, Helps You To Stream Video Files Stored On Multiplatform Sources In A Fully Customizable Way, You Will Have Full Control Over The Player.', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_keywords', 'value' => 'video player, video, jw player, jwplayer, google drive, google photos, youtube, facebook, internet archive, yandex disk, amazon drive, rumble, dropbox, mega, mp4, hls, mkv, streaming', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_tagline', 'value' => 'Free Videos Player multi-platform', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_og_image', 'value' => '/images/logo.png', 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_og_description', 'value' => 'Welcome to Forge Player, the best video player for free videos multi-platform.', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_og_title', 'value' => 'Forge Player', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_twitter_image', 'value' => '/images/logo.png', 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_twitter_description', 'value' => 'Welcome to Forge Player, the best video player for free videos multi-platform.', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_twitter_title', 'value' => 'Forge Player', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],

      ['id' => (string) Str::uuid(), 'key' => 'site_email', 'value' => 'admin@example.com', 'type' => 'email', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_phone', 'value' => '+1 123 456 7890', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_logo', 'value' => '/images/logo.png', 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'site_favicon', 'value' => '/images/favicon.png', 'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'enable_registration', 'value' => '0', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
      ['id' => (string) Str::uuid(), 'key' => 'enable_documentation', 'value' => '0', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
    ]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('settings');
  }
};
