<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $fillable = ["matricule", "service", "nom", "telephone", "lieu_ramassage", "pnt_adm", "pnt_dec"];
}
