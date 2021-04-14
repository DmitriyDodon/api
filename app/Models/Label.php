<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
      'name',
      'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function projects(){
        return $this->belongsToMany(Project::class);
    }

}
