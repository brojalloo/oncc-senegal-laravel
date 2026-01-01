<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = [
        'code',
        'nom',
        'chef_lieu',
        'superficie',
        'population',
        'latitude',
        'longitude',
    ];

    // Relations
    public function donneesClimatiques()
    {
        return $this->hasMany(DonneeClimatique::class);
    }

    public function donneesEconomiques()
    {
        return $this->hasMany(DonneeEconomique::class);
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}
