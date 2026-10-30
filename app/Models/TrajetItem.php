<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrajetItem extends Model
{
    protected $fillable = ['trajet_id', 'point'];

    public function trajet()
    {
        return $this->belongsTo(Trajet::class);
    }
}
