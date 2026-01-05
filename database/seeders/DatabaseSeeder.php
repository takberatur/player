<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Log\Logger;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $userSeeder = [
      [
        'name' => 'Admin User',
        'email' => 'admin@admin.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
      ],
      [
        'name' => 'Editor User',
        'email' => 'editor@editor.com',
        'password' => bcrypt('editor123'),
        'role' => 'editor',
        'email_verified_at' => now(),
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
      ],
    ];

    foreach ($userSeeder as $user) {
      User::create($user);
    }
  }
}
