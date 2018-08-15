<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';
    public $timestamps = false;
    public $appends = ['cid', 'thumb'];

    protected $fillable = ['id', 'isbn', 'name', 'name2', 'description', 'description2', 'author_id', 'series_id', 'type', 'format', 'updated_at', 'deliver_at'];

    public function getCidAttribute() {
        return base64_decode("VFNCQV9CVw==").str_pad($this->id, 14, "0", STR_PAD_LEFT)."_58";
    }

    public function getThumbAttribute()
    {
        return base64_decode("aHR0cHM6Ly9pMC53cC5jb20vc3RvcmUtdHNicC0wMDEuaGVyb2t1LmNvbS5zMy5hbWF6b25hd3MuY29tL3Byb2R1Y3Rpb24vZGVsaXZlcnk=")."/$this->cid/{$this->cid}_cover.jpg";
    }

    public function author() {
        return $this->belongsTo('App\Author');
    }

    public function series() {
        return $this->belongsTo('App\Series');
    }
}