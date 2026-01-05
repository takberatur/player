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
    Schema::create('subtitles', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('video_id')->constrained('videos')->onDelete('cascade');
      $table->string('language');
      $table->string('name');
      $table->text('url');
      $table->enum('type', ['vtt', 'srt']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subtitles');
  }
};