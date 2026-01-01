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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'nom',
        'prenom',
        'role',
        'region',
        'telephone',
        'statut',
        'email_verified_at',
        'verification_token',
        'verification_token_expires',
        'reset_token',
        'reset_token_expires',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'reset_token',
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
            'verification_token_expires' => 'datetime',
            'reset_token_expires' => 'datetime',
        ];
    }

    // Relations
    public function donneesClimatiques()
    {
        return $this->hasMany(DonneeClimatique::class, 'utilisateur_id');
    }

    public function donneesEconomiques()
    {
        return $this->hasMany(DonneeEconomique::class, 'utilisateur_id');
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class, 'createur_id');
    }
}
