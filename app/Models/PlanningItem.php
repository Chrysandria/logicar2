<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanningItem extends Model
{
    protected $fillable = ["employe_id","planning_id","expedition_id","ok","remarque"];

    public function employe(){
        return $this->belongsTo("employes");
    }

    public function planning(){
        return $this->belongsTo("plannings");
    }

    public function expedition(){
        return $this->belongsTo("expeditions");
    }
}
