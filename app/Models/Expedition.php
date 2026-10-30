<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    protected $fillable = ['date', 'hdr', 'trajet_id', 'car_id'];

    public function trajet()
    {
        return $this->belongsTo(Trajet::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function planning_items()
    {
        return $this->hasMany(PlanningItem::class);
    }
}
