<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // <-- Tambahkan ini

class User extends Authenticatable implements JWTSubject // <-- Tambahkan implements
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- Tambahkan kolom role untuk RBAC nanti
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- TAMBAHKAN 2 METHOD WAJIB INI DI BAWAH ---

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Menyimpan data role di dalam token JWT agar service lain bisa membacanya
        return [
            'role' => $this->role
        ];
    }
}