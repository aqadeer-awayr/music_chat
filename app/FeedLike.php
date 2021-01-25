<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedLike extends Model
{
    //
    public function user()
    {
    	return $this->belongsTo(User::class, 'liked_by', 'id');
    }
}
