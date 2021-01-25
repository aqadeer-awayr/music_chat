<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $fillable = [
    	'text',
        'song_id',
    	'playlist_id',
    	'url',
    	'user_id',
    ];
    protected $appends = ['liked_by_me', 'feed_likes_count', 'feed_comments_count'];
    public function feedImages()
    {
    	return $this->hasMany(FeedImage::class, 'feed_id', 'id');
    }
    public function feedVideos()
    {
    	return $this->hasMany(FeedVideo::class, 'feed_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function feedComments()
    {
        return $this->hasMany(FeedComment::class, 'feed_id', 'id');
    }
    public function feedLikes()
    {
        return $this->hasMany(FeedLike::class, 'feed_id', 'id');
    }
    public function getLikedByMeAttribute()
    {
        $like_by_loggedIn_count = FeedLike::where('feed_id', $this->id)->where('liked_by', \Auth::user()->id)->count();
        if($like_by_loggedIn_count > 0)
        {
            return 1;
        }
        return 0;
    }
    public function getFeedLikesCountAttribute()
    {
        return FeedLike::where('feed_id', $this->id)->count();
    }
    public function getFeedCommentsCountAttribute()
    {
        return FeedComment::where('feed_id', $this->id)->count();
    }
    public function feedSongs()
    {
        return $this->hasMany(FeedSong::class, 'feed_id', 'id');
    }
}
