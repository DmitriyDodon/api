<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'country_id',
        'verification_token',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function projects(){
        return $this->belongsToMany(Project::class);
    }

    public function project(){
        return $this->hasMany(Project::class);
    }

    public function labels(){
        return $this->hasMany(Label::class);
    }


}
