<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';
    public $timestamps = false;

    protected $fillable = ['id', 'isbn', 'name', 'name2', 'description', 'description2', 'author_id', 'series_id', 'type', 'format', 'updated_at', 'deliver_at'];

    public function author() {
        return $this->belongsTo('App\Author');
    }

    public function series() {
        return $this->belongsTo('App\Series');
    }
}