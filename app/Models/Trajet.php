<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trajet extends Model
{
    protected $fillable = ['designation'];

    public function trajet_items()
    {
        return $this->hasMany(TrajetItem::class, 'trajet_id');
    }

    public function expeditions()
    {
        return $this->hasMany(Expedition::class, 'trajet_id');
    }
}
