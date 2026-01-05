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
    Schema::create('custom_ad_vast_tags', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->text('video_url');
      $table->text(column: 'direct_link_ad');
      $table->text(column: 'file_url');
      $table->text(column: 'additional_direct_link_ad')->nullable();
      $table->string('tracking_url', 1000)->nullable();
      $table->string('duration')->default('00:00:15');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('custom_ad_vast_tags');
  }
};
