<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedComment extends Model
{
    //
    public function feed()
    {
        return $this->belongsTo(Feed::class, 'feed_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'commented_by', 'id');
    }
}
