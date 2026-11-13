<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $fillable = ['email', 'display_name', 'password', 'auth_version'];

    protected $hidden = ['password'];

    protected $attributes = ['auth_version' => 1];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'auth_version' => 'integer'];
    }
}
