<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    const PATH_MESSAGE_ATTACHMENTS = "message/attachments";

    protected $fillable = [
        'message_id', 'attachment'
    ];

    public function message()
    {
        return $this->belongsTo('App\ChatMessage', 'message_id');
    }

    public static function uploadAttachments($message_id, $attachments)
    {
        foreach ($attachments as $attachment) {
            $file_name = Storage::put(self::PATH_MESSAGE_ATTACHMENTS, $attachment);
            MessageAttachment::create([
                'message_id' => $message_id,
                'attachment' => $file_name
            ]);
        }
    }
}
