<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anomalie extends Model
{
    protected $fillable = ["car_id","description","start_at","end_at"];

    public function car(){
        return $this->belongsTo("cars");
    }
}
