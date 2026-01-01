<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'titre',
        'annee',
        'region_id',
        'type_rapport',
        'fichier_path',
        'resume',
        'createur_id',
    ];

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }
}
