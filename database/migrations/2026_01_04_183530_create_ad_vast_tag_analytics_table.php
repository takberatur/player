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
    Schema::create('ad_vast_tag_analytics', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('custom_ad_vast_tag_id')->constrained('custom_ad_vast_tags')->onDelete('cascade');
      $table->date('date');
      $table->unsignedBigInteger('impressions')->default(0);
      $table->unsignedBigInteger('clicks')->default(0);
      $table->unsignedBigInteger('starts')->default(0);
      $table->unsignedBigInteger('completes')->default(0);
      $table->timestamps();

      // Index for faster queries
      $table->index(['custom_ad_vast_tag_id', 'date']);
      // Ensure unique record per ad per date
      $table->unique(['custom_ad_vast_tag_id', 'date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ad_vast_tag_analytics');
  }
};
