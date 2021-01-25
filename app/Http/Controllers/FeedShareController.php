<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FeedShare;
use App\User;
use Validator;
use Auth;
use App\Feed;
use App\FeedImage;
use App\FeedVideo;

class FeedShareController extends Controller
{
    public function share(Request $request)
    {
    	$rules =  [
            'share_text' => 'string',
            'feed_id' => 'integer|min:1|required',
        ];
    	$messages = [
    		'feed_id.integer' => 'invalid feed shared',
    		'feed_id.min' => 'invalid feed shared',
    	];
		$validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }
    	$feed_share = new FeedShare();
    	$feed_share->user_id = Auth::user()->id;
    	$feed_share->feed_id = $request->feed_id;
    	$feed_share->share_text = $request->share_text;
    	$feed_share->save();

        //feed reposting again
        $feed = Feed::find($request->feed_id);
        $feed_again = new Feed();
        $feed_again->text = $feed->text;
        $feed_again->song_id = $feed->song_id;
        $feed_again->playlist_id = $feed->playlist_id;
        $feed_again->url = $feed->url;
        $feed_again->user_id = Auth::user()->id;
        $feed_again->is_shared = 1;
        $feed_again->save();

        $image_folder = '/uploads/feeds/images/';
        $video_folder = '/uploads/feeds/videos/';
        if($feed->feedImages->count() > 0)
        {
            //image saving in database
            foreach($feed->feedImages as $img_key=> $feed_image)
            {
                $shared_feed_image = new FeedImage();
                $shared_feed_image->image = $feed_image->image;
                $shared_feed_image->image_folder_name = $image_folder;
                $shared_feed_image->feed_id = $feed_again->id;
                $shared_feed_image->save();
            }   
        }
        if($feed->feedVideos->count() > 0)
        {
            //video saving in database
            foreach($feed->feedVideos as $vid_key=> $feed_video)
            {
                $shared_feed_video = new FeedVideo();
                $shared_feed_video->video = $feed_video->video;
                $shared_feed_video->video_folder_name = $video_folder;
                $shared_feed_video->feed_id = $feed_again->id;
                $shared_feed_video->save();
            }
        }
    	return response()->json([
    		'status' => 200,
    		'message' => 'feed shared successfully',
    	]);
    }
}
