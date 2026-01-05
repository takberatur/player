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
    Schema::create('video_view_history', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('video_id');
      $table->date('view_date');
      $table->integer('view_count')->default(0);
      $table->timestamps();

      $table->foreign('video_id')
        ->references('id')
        ->on('videos')
        ->onDelete('cascade');

      $table->index('view_date');
      $table->index(['video_id', 'view_date']);
      $table->unique(['video_id', 'view_date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('video_view_history');
  }
};