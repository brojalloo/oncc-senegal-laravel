<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    protected $fillable = [
        'region_id',
        'type_alerte',
        'niveau',
        'description',
        'date_debut',
        'date_fin',
        'recommandations',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
