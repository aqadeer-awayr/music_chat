<?php

namespace App\Http\Controllers;

use App\Feed;
use App\User;
use App\UserGenre;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'invalid credentials',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'could not create token'
            ], 500);
        }
        $user = Auth::user();
        $data = [
            'token' => $token,
            'userId' => $user->id,
            'profile_image' => $user->profile_image,
        ];
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'login successful',
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'profile_image' => 'mimes:png,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::create([
            'user_name' => $request->get('user_name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'profile_image' => $request->get('profile_image'),
        ]);
        if ($request->hasFile('profile_image')) {

            $profile_image = $request->file('profile_image');
            $hashed_image_name = str_replace('/', '', str_replace('.', '', Hash::make($user->id . '-' . $user->user_name)));
            $filename = $hashed_image_name . '.' . $profile_image->getClientOriginalExtension();
            Image::make($profile_image)->save(public_path('/uploads/users/profile/' . $filename));
            $user->profile_image = $filename;
            $user->update();
        }

        $token = JWTAuth::fromUser($user);
        $data = [
            'token' => $token,
            'userId' => $user->id,
            'profile_image' => $user->profile_image,
        ];
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user generated successfully',
        ], 200);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'user_not_found',
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'message' => 'token_expired'
            ], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'message' => 'token_invalid',
            ], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'message' => 'token_absent',
            ], $e->getStatusCode());
        }
        $data = [
            'user' => $user,
        ];
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user data',
        ]);
    }

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'string|max:255',
            'email' => 'required|string|email|max:255',
            'profile_image' => 'mimes:png,jpeg',
        ]);
        if ($request->is_gmail_login != 1) {
            $request->request->add(['is_facebook_login' => 1]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
        //find or create
        $existing_user = User::where('email', $request->email)->get();
        if ($existing_user->count() == 0) {
            $user = User::create([
                'user_name' => $request->get('user_name'),
                'email' => $request->get('email'),
                'profile_image' => $request->get('profile_image'),
            ]);
            if ($request->hasFile('profile_image')) {
                $profile_image = $request->file('profile_image');
                $hashed_image_name = str_replace('/', '', str_replace('.', '', Hash::make($user->id . '-' . str_replace(' ', '', $user->user_name))));
                $filename = $hashed_image_name . '.' . $profile_image->getClientOriginalExtension();
                Image::make($profile_image)->save(public_path('/uploads/users/profile/' . $filename));
                $user->profile_image = $filename;
                $user->update();
            }
            $token = JWTAuth::fromUser($user);
        } else {
            if (!$token = JWTAuth::fromUser($existing_user->first())) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            $user = $existing_user->first();
        }
        $data = [
            'token' => $token,
            'userId' => $user->id,
        ];
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'login successful',
        ], 200);
    }
    public function search(Request $request)
    {
        // $user = new User();
        // if ($request->has('query') && ($request->get('query') != null || $request->get('query') != '')) {
        //     $users = $user->where('id', '!=', $request->get('userId'))->where('user_name', 'LIKE', '%' . $request->get('query') . '%')->orWhere('email', $request->get('query'));
        // }
        // $userId = $request->get('userId');
        $users = User::searchUsers($request->all());
        return response()->json([
            'data' => $users,
            'status' => 200,
            'message' => 'fetched results'
        ]);
    }
    public function getProfile(Request $request)
    {
        $searched_user = User::where('id', $request->get('searchedUserId'))->first();
        $feeds = Feed::where('user_id', $request->get('searchedUserId'))->with(['feedImages', 'feedVideos', 'feedLikes', 'feedComments'])->get();
        foreach ($feeds as $feed) {
            $feed->user_name = $searched_user->user_name;
            $feed->profile_image = $searched_user->profile_image;
            foreach ($feed->feedComments as $feed_comment) {
                $feed_comment->user_name = $feed_comment->user->user_name;
                $feed_comment->profile_image = $feed_comment->user->profile_image;
                $feed_comment->user_created_at = $feed_comment->user->created_at;
            }
        }
        $data = new User();
        $user = User::where('id', $request->get('searchedUserId'))->with(['userArtists'])->get()->first();
        $data->id = $user->id;
        $data->email = $user->email;
        $data->user_name = $user->user_name;
        $data->profile_image = $user->profile_image;
        $data->bio = $user->bio;
        $data->followers_count = $user->followers_count;
        $data->followings_count = $user->followings_count;
        $data->user_artists = $user->user_artists == null ? [] : $user->user_artists;
        $data->user_feeds = $feeds;
        $data->is_follower = $user->is_follower($request->get('userId'));
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user profile result'
        ]);
    }
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'string|max:255',
            'bio' => 'string|min:2|max:255',
            'password' => 'string|min:6',
            'profile_image' => 'mimes:png,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $user = User::find($request->userId);
        if ($request->password) {
            $request->request->add(['password' => Hash::make($request->password)]);
        }
        $user->update($request->all());
        if ($request->geners) {
            foreach (explode(',', $request->geners) as $genre) {
                $user_genre = new UserGenre();
                $user_genre->user_id = $user->id;
                $user_genre->genre = $genre;
            }
        }
        if ($request->hasFile('profile_image')) {
            $profile_image = $request->file('profile_image');
            $hashed_image_name = str_replace('/', '', str_replace('.', '', Hash::make($user->id . '-' . $user->user_name)));
            $filename = $hashed_image_name . '.' . $profile_image->getClientOriginalExtension();
            Image::make($profile_image)->save(public_path('/uploads/users/profile/' . $filename));
            $user->profile_image = $filename;
            $user->update();
        }
        return response()->json([
            'status' => 200,
            'message' => 'user profile updated successfully'
        ]);
    }
    public function passwordChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'password:api',
            'new_password' => 'required|string|min:6',
        ]);
        $user = Auth::user();

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
        // if (!Hash::check($request->old_password, $user->password)) {
        //     return response()->json([
        //         'status' => 400,
        //         'message' => 'Old password mismatch'
        //     ], 401);
        // }
        $request->request->add(['password' => Hash::make($request->new_password)]);
        $user->password = $request->password;
        $user->update();
        return response()->json([
            'status' => 200,
            'message' => 'Password changed successfully',
        ]);
    }
}
