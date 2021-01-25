<?php

namespace App;

use App\Events\LikeMessageEvent;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MessageLikeOrUnLike extends Model
{
    const TYPE_LIKE = "like";
    const TYPE_UNLIKE = "unlike";

    protected $fillable = [
        'message_id', 'user_id', 'type'
    ];

    public function message()
    {
        return $this->belongsTo('App\ChatMessage', 'message_id');
    }

    public static function addLikeOrUnlike($data)
    {
        $messageLikeOrUnlike = MessageLikeOrUnLike::where('message_id', $data['message_id'])
            ->where('user_id', $data['user_id'])->first();
        if ($messageLikeOrUnlike) {
            $data['id'] = $messageLikeOrUnlike->id;
            $message = MessageLikeOrUnLike::updateOrCreate(['id' => $data['id']], $data);
            self::sendBroadcasts($message, Auth::user());
            return $message;
        }

        $message = MessageLikeOrUnLike::create($data);
        self::sendBroadcasts($message, Auth::user());
        return $message;
    }

    public static function sendBroadcasts($message, $user)
    {
        try {
            broadcast(new LikeMessageEvent($message, $user))->toOthers();
        } catch (Exception $exception) {
        }
    }
}
