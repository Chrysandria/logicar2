<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    protected $fillable = ['car_id', 'date', 'km'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
