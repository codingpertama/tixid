<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cinema extends Model
{
    //mendaftarkan softdeletes
    use SoftDeletes;

    // mendaftarkan detail data (column) agar data data tidak bisa diisi
    protected $fillable = ['name', 'location'];
}
