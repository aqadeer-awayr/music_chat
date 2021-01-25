<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use App\MessageLikeOrUnLike;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    // send message and notification to user or users
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', Rule::exists('groups', 'id')->whereNull('deleted_at')],
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $sender_id = Auth()->user()->id;
        $message = ChatMessage::addMessage(
            $sender_id,
            $request->group_id,
            $request->message,
            $request->link,
            $request->attachments,
            $request->quote_message_id
        );
        $message = ChatMessage::getFullMessage($message->id);
        ChatMessage::sendBroadcasts($message);
        return $this->returnSuccess('Message Sent', $message);
    }

    // Get messages using group id
    public function getChatWithGroupId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|integer|exists:App\Group,id',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $chat = ChatMessage::getChat($request->group_id);
        return $this->returnSuccess('chat', $chat);
    }

    // like or unlike chat messages
    public function likeOrUnlikeMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|integer|exists:App\ChatMessage,id',
            'type' => 'required|in:like,unlike',
        ]);
        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $data = [
            'message_id' => $request->message_id,
            'user_id' => Auth()->user()->id,
            'type' => $request->type,
        ];
        // dd($data);
        $messageLikeOrUnlike = MessageLikeOrUnLike::addLikeOrUnlike($data);
        return $this->returnSuccess("Message {$request->type} Successfully");
    }
}
