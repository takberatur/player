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
    Schema::table('videos', function (Blueprint $table) {
      $table->boolean("enable_popunder_ad")->default(false)->after("ad_vast");
      $table->text("popunder_ad_code")->nullable()->after("enable_popunder_ad");
      $table->text("additional_ad_vast")->nullable()->after("popunder_ad_code");
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('videos', function (Blueprint $table) {
      $table->dropColumn("enable_popunder_ad");
      $table->dropColumn("popunder_ad_code");
      $table->dropColumn("additional_ad_vast");
    });
  }
};
