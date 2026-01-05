<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('videos', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('title');
      $table->text('original_link');
      $table->text('encode_link')->nullable();
      $table->string('poster', 1000)->default(asset('images/default-thumbnail.jpg'));
      $table->enum('type', [
        'google_drive',
        'yandex_disk',
        'amazon_drive',
        'archive',
        'facebook',
        'google_photo',
        'rumble',
        'dropbox',
        'youtube',
        'mega_disk',
        'm3u8',
        'mp4',
        'mkv',
        'ts',
        'mpd'
      ])->default('google_drive');
      $table->text('ad_vast')->nullable();
      $table->boolean('enable_button_download')->default(false);
      $table->text('download_link')->nullable();
      $table->json('sources_json')->nullable();
      $table->unsignedBigInteger('views')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('videos');
  }
};