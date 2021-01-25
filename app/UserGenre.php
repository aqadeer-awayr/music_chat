<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGenre extends Model
{
    protected $fillable = [
        'genre',
        'user_id'
    ];
}
