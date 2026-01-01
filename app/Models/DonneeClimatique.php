<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonneeClimatique extends Model
{
    protected $table = 'donnees_climatiques';

    protected $fillable = [
        'region_id',
        'annee',
        'type_indicateur',
        'valeur',
        'unite',
        'source',
        'commentaire',
        'statut',
        'utilisateur_id',
    ];

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
