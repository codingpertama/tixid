<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = ['cinema_id', 'movie_id', 'hours', 'price'];

    // array [], json {} / "[]"
    // di migration support json biar si data json menggunakan format array

    protected function casts():array {
        return [
            'hours' => 'array'
        ];
    }

    // karna cinema posisi one jadi tunggal
    public function cinema() {
        // karna schedule ada di posisi dua gunakan belongsTo untuk menyambungkan
        return $this->belongsTo(Cinema::class);
    }
    public function movie() {
        return $this->belongsTo(Movie::class);
    }
}

