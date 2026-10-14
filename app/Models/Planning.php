<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $fillable = ["dps", "hps"];

    public function planning_items(){
        return $this->hasMany("planning_items");
    }
}
