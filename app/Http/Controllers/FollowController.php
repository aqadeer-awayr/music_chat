<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Following;

class FollowController extends Controller
{
    public function store(Request $request)
    {
        $already_following_users = Following::where('user_id', $request->userId)->where('following_user_id', $request->followUserId)->count();
        // dd($already_following_users);
        if($already_following_users == 0)
        {
            $following = new Following();
            $following->user_id = $request->userId;
            $following->following_user_id = $request->followUserId;
            $following->save();
            return response()->json([
                'status' => 200,
                'message' => 'User followed successfully',
                ]);
        }
        else
        {
            $user = User::find($request->userId)->followings->where('following_user_id', $request->followUserId)->first()->delete();
            // dd($user);
            return response()->json([
                'status' => 200,
                'message' => 'User unfollowed successfully',
            ]);
        }
        return response()->json([
            'status' => 400,
            'message' => 'Already following this user',
        ], 400);
    }
    public function getFollowings(Request $request)
    {
        $user_data = new User();
        $user_following_ids = User::find($request->get('userId'))->followings->pluck('following_user_id');
        $users = User::whereIn('id', $user_following_ids)->get();
        $data = [];
        foreach($users as $user)
        {
            $user_data->id = $user->id;
            $user_data->email = $user->email;
            $user_data->user_name = $user->user_name;
            $user_data->profile_image = $user->profile_image;
            $user_data->bio = $user->bio;
            $user_data->followers_count = $user->followers_count;
            $user_data->followings_count = $user->followings_count;
            $user_data->user_artists = $user->user_artists == null ? [] : $user->user_artists;
            $user_data->user_feeds = [];
            $user_data->is_follower = $user->is_follower($request->get('userId'));
            $data[] = $user_data;
            $user_data = new User();
        }

        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user followings',
        ]);
    }
    public function getFollowers(Request $request)
    {
        $user_data = new User();
        $user_follower_ids = User::find($request->get('userId'))->followers->pluck('user_id');
        $users = User::whereIn('id', $user_follower_ids)->get();
        $data = [];
        foreach($users as $user)
        {
            $user_data->id = $user->id;
            $user_data->email = $user->email;
            $user_data->user_name = $user->user_name;
            $user_data->profile_image = $user->profile_image;
            $user_data->bio = $user->bio;
            $user_data->followers_count = $user->followers_count;
            $user_data->followings_count = $user->followings_count;
            $user_data->user_artists = $user->user_artists == null ? [] : $user->user_artists;
            $user_data->user_feeds = [];
            $user_data->is_follower = $user->is_follower($request->get('userId'));
            $data[] = $user_data;
            $user_data = new User();
        }
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user followers',
        ]);
    }
    public function destroy($userId, $followerId)
    {
        $user = User::find($userId)->followers->where('following_user_id', $followerId)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'follower deleted',
        ]);
    }
}
