<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = 'series';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function contents() {
        return $this->hasMany('App\Content');
    }
}