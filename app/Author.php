<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'authors';
    public $timestamps = false;

    protected $fillable = ['name', 'alt'];

    public function contents() {
        return $this->hasMany('App\Content');
    }
}