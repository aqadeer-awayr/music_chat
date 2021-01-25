<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_public',
        'is_collaborative',
        'created_by',
        'spotify_playlist_id',
        'playlist_image',
    ];
}
