<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
      'country_code',
      'country_name',
      'continent_id'
    ];

    public function users(){
        return $this->hasMany(User::class);
    }

    public function continent(){
        return $this->belongsTo(Continent::class);
    }
}
