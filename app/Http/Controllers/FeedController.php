<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Feed;
use App\FeedImage;
use App\FeedVideo;
use Validator;
use File;
use Image;
use App\User;
use App\FeedLike;
use App\FeedComment;
use Hash;
use App\FeedSong;

class FeedController extends Controller
{
    public function store(Request $request)
    {
    	$rules =  [
            'text' => 'string',
            'song_id' => 'string|max:255',
            'playlist_id' => 'string|max:255',
            'url' => 'string',
            'videos.*' => 'mimes:mp4,webm,mkv,qt,flv,wmv,mov',
            'images.*' => 'mimes:png,jpeg,gif,jpg',
        ];
    	$messages = [
    		//
    	];
		$validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
        $request->request->add([
        	'user_id' => Auth::user()->id,
        ]);
        $feed = Feed::create($request->all());

        // folder name initializations
        $image_folder = '/uploads/feeds/images/';
        $video_folder = '/uploads/feeds/videos/';
        $feed_song_folder = '/uploads/feeds/songs/images/';

        //complete path for image creation
        $image_path = public_path().$image_folder;
        $video_path = public_path().$video_folder;
        $feed_song_path = public_path().$feed_song_folder;

        //physical folder creation on local directory or serve directory
        if (!File::exists($image_path)) {
            File::makeDirectory($image_path, $mode = 0777, true, true);
        }
        if (!File::exists($video_path)) {
            File::makeDirectory($video_path, $mode = 0777, true, true);
        }
        if (!File::exists($feed_song_path)) {
            File::makeDirectory($feed_song_path, $mode = 0777, true, true);
        }
        //image saving in database and directory
        if($request->images)
        {
        	foreach($request->images as $img_key=> $image)
		    {
                $hashed_image_name = str_replace('/', '', str_replace('.', '', Hash::make($feed->id.'-'.$img_key)));
		    	$feed_image = new FeedImage();
		        $filename = $hashed_image_name.'.'.$image->getClientOriginalExtension();
		        Image::make($image)->save(public_path($image_folder . $filename));
		        $feed_image->image = $filename;
		        $feed_image->image_folder_name = $image_folder;
		        $feed_image->feed_id = $feed->id;
		        $feed_image->save();
		    }	
        }
        if($request->videos)
        {
	        //video saving in database and directory
	        foreach($request->videos as $vid_key=> $video)
	        {
                $hashed_video_name = str_replace('/', '', str_replace('.', '', Hash::make($feed->id.'-'.$vid_key)));
	        	$feed_video = new FeedVideo();
	        	$video_filename = $hashed_video_name.'.'.$video->getClientOriginalExtension();
	            $video->move(public_path($video_folder), $video_filename);
	            $feed_video->video = $video_filename;
	            $feed_video->video_folder_name = $video_folder;
		        $feed_video->feed_id = $feed->id;
	            $feed_video->save();
	        }
	    }
        $feed_song = new FeedSong();
        $feed_song->feed_id = $feed->id;
        $feed_song->name = $request->song_name;
        $feed_song->preview_url = $request->song_preview_url;
        $feed_song->spotify_song_id = $request->spotify_song_id;
        $feed_song->image = $request->song_image;
        $feed_song->save();

        // if($request->hasFile('song_image')){
        //     $song_image = $request->file('song_image');
        //     $hashed_image_name = str_replace('/', '', str_replace('.', '', Hash::make($feed_song->id.'-'.$feed_song->song_name)));
        //     $filename = $hashed_image_name.'.'.$song_image->getClientOriginalExtension();
        //     Image::make($song_image)->save(public_path($feed_song_folder . $filename));
        //     $feed_song->image_folder_name = $feed_song_folder;
        //     $feed_song->image = $filename;
        //     $feed_song->update();
        // }
        return response()->json([
        	'status' => 200,
        	'message' => 'Feed added successfully',
        ]);
    }
    public function find(Request $request)
    {
    	$feed = Feed::where('id', $request->feed_id)->with(['feedImages', 'feedVideos', 'feedLikes', 'feedComments', 'feedSongs'])->first();
        $feed->user_name = User::find($feed->user_id)->user_name;
        $feed->profile_image = User::find($feed->user_id)->profile_image;
        foreach($feed->feedComments as $feed_comment)
        {
            $feed_comment->user_name = $feed_comment->user->user_name;
            $feed_comment->profile_image = $feed_comment->user->profile_image;
            $feed_comment->user_created_at = $feed_comment->user->created_at;
        }
    	return response()->json([
    		'data' => $feed,
    		'status' => 200,
    		'message' => 'feeds fetched successfully',
    	]);
    }
    public function all()
    {
        $user_ids = Auth::user()->followings->pluck('following_user_id');
        $user_ids[] = Auth::user()->id;
    	$feeds = Feed::whereIn('user_id', $user_ids)->with(['feedImages', 'feedVideos', 'feedLikes', 'feedComments', 'feedSongs'])->orderBy('updated_at', 'desc')->get();
        foreach($feeds as $feed)
        {
            $feed->user_name = $feed->user->user_name;
            $feed->profile_image = $feed->user->profile_image;
            foreach($feed->feedComments as $feed_comment)
            {
                $feed_comment->user_name = $feed_comment->user->user_name;
                $feed_comment->profile_image = $feed_comment->user->profile_image;
                $feed_comment->user_created_at = $feed_comment->user->created_at;
            }
        }
    	return response()->json([
    		'data' => $feeds,
    		'status' => 200,
    		'message' => 'feeds fetched successfully',
    	]);
    }
    public function feedLike(Request $request)
    {
        $feed_liked = FeedLike::where('liked_by', $request->liked_by)->where('feed_id', $request->feed_id);
        if($feed_liked->count() > 0)
        {
            $feed_liked->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Feed unliked successfully',
            ]);
        }
        // if($feed_liked->count() > 0)
        // {
        //     return response()->json([
        //         'status' => 200,
        //         'message' => 'Feed already liked',
        //     ]);
        // }
        $feed_like = new FeedLike();
        $feed_like->liked_by = $request->liked_by;
        $feed_like->feed_id = $request->feed_id;
        $feed_like->save();
        return response()->json([
            'status' => 200,
            'message' => 'Feed liked successfully',
        ]);
    }
    public function feedComment(Request $request)
    {
        $feed_comment = new FeedComment();
        $feed_comment->comment = $request->comment;
        $feed_comment->commented_by = $request->commented_by;
        $feed_comment->feed_id = $request->feed_id;
        $feed_comment->save();
        $feed_comment->user_name = $feed_comment->user->user_name;
        $feed_comment->profile_image = $feed_comment->user->profile_image;
        $feed_comment->user_created_at = $feed_comment->user->created_at;
        return response()->json([
            'data' => $feed_comment,
            'status' => 200,
            'message' => 'Commented on feed successfully',
        ]);
    }
    public function feedCommentRemove(Request $request)
    {
        FeedComment::where('id', $request->feed_comment_id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Commented deleted successfully',
        ]);
    }
    public function feedSearch(Request $request)
    {
        $user_ids = Auth::user()->followings->pluck('following_user_id');
        $user_ids[] = Auth::user()->id;
        $searched_user_ids = User::whereIn('id', $user_ids)->where('user_name', 'LIKE', '%'.$request->get('query'). '%')->orWhere('email', 'LIKE', '%'.$request->get('query'). '%')->pluck('id');

        $feeds = Feed::where('text', 'LIKE', '%'.$request->get('query'). '%')->whereIn('user_id', $searched_user_ids)->with(['feedImages', 'feedVideos', 'feedLikes', 'feedComments', 'feedSongs'])->get();
        foreach($feeds as $feed)
        {
            $feed->user_name = User::where('id', $feed->user_id)->first()->user_name;
            $feed->profile_image = User::where('id', $feed->user_id)->first()->profile_image;
            foreach($feed->feedComments as $feed_comment)
            {
                $feed_comment->user_name = $feed_comment->user->user_name;
                $feed_comment->profile_image = $feed_comment->user->profile_image;
                $feed_comment->user_created_at = $feed_comment->user->created_at;
            }
        }
        return response()->json([
            'data' => $feeds,
            'status' => 200,
            'message' => 'Searched feeds fetched'
        ]);
    }
    public function destroy(Request $request)
    {
        \DB::beginTransaction();
        try {
            //folder name
            $image_folder = '/uploads/feeds/images/';
            $video_folder = '/uploads/feeds/videos/';

            $feed = Feed::where('id', $request->feed_id);
            $image_path = $image_folder.$feed->first()->image;  // Value is not URL but directory file path
            $video_path = $video_folder.$feed->first()->video;
            if(File::exists($image_path)) {
                File::delete($image_path);
            }
            if(File::exists($video_path)) {
                File::delete($video_path);
            }
            $feed->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'feed deleted successfully',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            // something went wrong
            return response()->json([
                'status' => 400,
                'message' => $e,
            ]);
        }

    }
}
