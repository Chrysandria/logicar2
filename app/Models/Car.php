<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['immatriculation', 'is_available'];

    public function anomalies()
    {
        return $this->hasMany(Anomalie::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    public function expeditions()
    {
        return $this->hasMany(Expedition::class);
    }
}
