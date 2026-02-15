<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasFactory;
    protected $guard = 'admin';
    
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Trading-related relationships
    public function tradeOverrides()
    {
        return $this->hasMany(TradeOverride::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'assign_to');
    }
}
