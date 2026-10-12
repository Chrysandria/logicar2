<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrajetItem extends Model
{
    protected $fillable = ["trajet_id","point"];

    public function trajet(){
        return $this->belongsTo("trajets");
    }
}
