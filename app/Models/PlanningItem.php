<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanningItem extends Model
{
    protected $fillable = ["employe_id","planning_id","expedition_id","ok","remarque"];

    public function employe(){
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function planning(){
        return $this->belongsTo(Planning::class, 'planning_id');
    }

    public function expedition(){
        return $this->belongsTo("expeditions");
    }
}
