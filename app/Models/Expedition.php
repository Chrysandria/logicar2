<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    protected $fillable = ['date', 'hdr', 'trajet_id', 'car_id'];

    public function trajet()
    {
        return $this->belongsTo(Trajet::class, 'trajet_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function planning_items()
    {
        return $this->hasMany(PlanningItem::class, 'expedition_id');
    }
}
