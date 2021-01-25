<?php

namespace App;

use App\Events\NewMessage;
use App\Events\NewMessageNotification;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class ChatMessage extends Model
{
    const MESSAGES_PER_PAGE = 30;
    protected $fillable = [
        'group_id',
        'sender_id',
        'message',
        'link',
        'is_read',
        'quote_message_id'
    ];

    protected $appends = ['likes_count', 'is_liked', 'is_unliked'];

    public function attachments()
    {
        return $this->hasMany('App\MessageAttachment', 'message_id');
    }

    public function sender()
    {
        return $this->hasOne('App\User', 'id', 'sender_id');
    }

    public function quote_message_id()
    {
        return $this->hasOne('App\ChatMessage', 'id', 'quote_message_id')->with('attachments', 'sender');
    }

    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id');
    }

    public function likes()
    {
        return $this->hasMany('App\MessageLikeOrUnLike', 'message_id')
            ->where('type', MessageLikeOrUnLike::TYPE_LIKE);
    }

    public function unlikes()
    {
        return $this->hasMany('App\MessageLikeOrUnLike', 'message_id')
            ->where('type', MessageLikeOrUnLike::TYPE_UNLIKE);
    }

    public static function addMessage($sender_id, $group_id, $message, $link = null, $attachments = null, $quote_message_id = null)
    {
        $data = [
            'sender_id' => $sender_id,
            'group_id' => $group_id,
            'message' => $message,
            'link' => $link,
            'quote_message_id' => $quote_message_id
        ];
        $chatMessage = ChatMessage::create($data);
        if ($attachments) {
            MessageAttachment::uploadAttachments($chatMessage->id, $attachments);
        }
        return $chatMessage;
    }

    public static function sendBroadcasts($message)
    {
        try {
            broadcast(new NewMessage($message))->toOthers();
            broadcast(new NewMessageNotification($message))->toOthers();
        } catch (Exception $exception) {
        }
    }

    public static function getFullMessage($message_id)
    {
        return ChatMessage::where('id', $message_id)->with('attachments', 'sender', 'group', 'quote_message_id')->first();
    }

    public static function getChat($group_id)
    {
        $group = Group::find($group_id);
        $messages = ChatMessage::where('group_id', $group->id)->with('attachments', 'sender', 'quote_message_id')->latest()->paginate(self::MESSAGES_PER_PAGE);
        $group['messages'] = $messages->items();
        $group['total'] = $messages->total();
        $group['current_page'] = $messages->currentPage();
        $group['per_page'] = $messages->perPage();
        return $group;
    }

    function getLikesCountAttribute()
    {
        $likes = MessageLikeOrUnLike::where('message_id', $this->id)->where('type', MessageLikeOrUnLike::TYPE_LIKE)->count();
        $unlikes = MessageLikeOrUnLike::where('message_id', $this->id)->where('type', MessageLikeOrUnLike::TYPE_UNLIKE)->count();
        return $likes - $unlikes;
    }

    function getIsLikedAttribute()
    {
        $like = MessageLikeOrUnLike::where('message_id', $this->id)
            ->where('user_id', Auth()->user()->id)
            ->where('type', MessageLikeOrUnLike::TYPE_LIKE)->first();
        return ($like) ? true : false;
    }

    function getIsUnlikedAttribute()
    {
        $unlike = MessageLikeOrUnLike::where('message_id', $this->id)
            ->where('user_id', Auth()->user()->id)
            ->where('type', MessageLikeOrUnLike::TYPE_UNLIKE)->first();
        return ($unlike) ? true : false;
    }
}
