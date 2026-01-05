<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, TwoFactorAuthenticatable, HasUuids;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'id',
    'name',
    'email',
    'password',
    'avatar',
    'role',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'two_factor_confirmed_at' => 'datetime',
    ];
  }
  public function isAdmin(): bool
  {
    return $this->role === 'admin';
  }
  public function isEditor(): bool
  {
    return $this->role === 'editor';
  }
  public function isUser(): bool
  {
    return $this->role === 'user';
  }
  public function scopeAdmins($query)
  {
    return $query->where('role', 'admin');
  }
  public function scopeEditors($query)
  {
    return $query->where('role', 'editor');
  }
  public function scopeRegularUsers($query)
  {
    return $query->where('role', 'user');
  }
}