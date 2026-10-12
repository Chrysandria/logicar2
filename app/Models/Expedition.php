<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    protected $fillable = ["date","hdr","trajet_id","car_id"];

    public function trajet(){
        return $this->belongsTo("trajets");
    }

    public function car(){
        return $this->belongsTo("car");
    }

    public function planning_items(){
        return $this->hasMany("planning_items");
    }
}
