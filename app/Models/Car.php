<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ["immatriculation","is_available"];

    public function anomalies(){
        return $this->hasMany("anomalies");
    }

    public function mouvements(){
        return $this->hasMany("mouvements");
    }

    public function expeditions(){
        return $this->hasMany("expeditions");
    }
}
