<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Constantes
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_REMITENTE = 'REMITENTE';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_FIRED = 'fired';

    // Atributos
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'cellphone',
        'brand_name',
        'brand_slug',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Helpers
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isRemitente()
    {
        return $this->role === self::ROLE_REMITENTE;
    }

     /* Relaciones */
    public function clinicalImages()
    {
        return $this->hasMany(ClinicalImage::class);
    }

}
