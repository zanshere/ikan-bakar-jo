<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function restocks()
    {
        return $this->hasMany(Restock::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeOwner($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeUser($query)
    {
        return $query->where('role', 'user');
    }

    // Methods
    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function getRoleBadgeAttribute()
    {
        return match($this->role) {
            'owner' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Owner</span>',
            'user' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">User</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }
}
