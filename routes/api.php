<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::get('open', 'DataController@open');
// password reset routes
Route::post('password/email/reset', 'PasswordResetController@create');
Route::get('password/reset/find/{token}', 'PasswordResetController@find');
Route::post('password/reset', 'PasswordResetController@reset');

Route::post('social-login', 'UserController@socialLogin');

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('user', 'UserController@getAuthenticatedUser');
    Route::get('closed', 'DataController@closed');
    Route::post('users/search', 'UserController@search');
    Route::post('user/profile', 'UserController@getProfile');
    Route::post('user/profile/update', 'UserController@updateProfile');

    //artists
    Route::get('artists/search', 'ArtistController@searchArtist');
    Route::get('find/artist/{artistId}', 'ArtistController@find');
    Route::post('user/favourite/artist', 'ArtistController@userArtistSave');

    //followings
    Route::post('follow/user', 'FollowController@store');
    Route::get('followings', 'FollowController@getFollowings');
    Route::get('followers', 'FollowController@getFollowers');
    // Route::delete('follower/{userId}/{followerId}', 'FollowController@destroy');

    //tracks
    Route::get('tracks/search', 'TrackController@searchTrack');

    //feeds
    Route::post('feed/add', 'FeedController@store');
    Route::get('feeds', 'FeedController@all');
    Route::post('feed/find', 'FeedController@find');
    Route::post('feed/delete', 'FeedController@destroy');

    //playlist
    Route::post('playlist/create', 'PlaylistController@store');
    Route::get('playlists', 'PlaylistController@spotifyUserPlaylists');

    //group
    Route::post('group/create', 'GroupController@store');
    Route::get('groups', 'GroupController@userGroups');
    Route::get('groups/search', 'GroupController@search');
    Route::post('group/leave', 'GroupController@leave');
    Route::post('group/find', 'GroupController@find');
    Route::delete('group/delete/{id}', 'GroupController@destroy');

    //group members
    Route::post('kick/member', 'GroupController@kickMember');
    Route::post('add/members', 'GroupController@addMembers');
    Route::post('list/members', 'GroupController@listMembers');

    //password change
    Route::post('password/change', 'UserController@passwordChange');

    //feed like
    Route::post('feed/like', 'FeedController@feedLike');

    //feed comment
    Route::post('feed/comment', 'FeedController@feedComment');
    Route::post('feed/comment/remove', 'FeedController@feedCommentRemove');

    //share
    Route::post('feed/share', 'FeedShareController@share');

    //search feed
    Route::post('feed/search', 'FeedController@feedSearch');

    //chat
    Route::post('send-message', 'ChatController@sendMessage');
    Route::post('get-chat-with-group-id', 'ChatController@getChatWithGroupId');
    Route::post('like-or-unlike-message', 'ChatController@likeOrUnlikeMessage');
});
Route::post('swap', 'TokenManagerController@swap');
