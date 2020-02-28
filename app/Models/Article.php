<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $guarded = ['id'];

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }
}
