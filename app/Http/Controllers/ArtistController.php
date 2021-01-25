<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rennokki\Larafy\Larafy;
use App\UserArtist;

class ArtistController extends Controller
{
    public function searchArtist(Request $request)
    {
        $api = new Larafy();
        //$limit = 15;
        //$offset = 5;
        $artist_name = $request->get('artist_name');
        try {
            $searched_result = $api->searchArtists($artist_name);
        } catch(\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
            return response()->json([
                'message' => $e->getAPIResponse()->error->message,
                'status' => $e->getAPIResponse()->error->status,
            ]);
        }
        return response()->json([
            'data' =>  $searched_result->items,
            'status' => 200,
            'message' => 'Artist searched successfully',
        ], 200);
    }
    public function find($artistId)
    {
        $api = new Larafy();
        $artist = $api->getArtist($artistId);
        return response()->json([
            'data' => $artist, 
            'status' => 200,
            'message' => 'Artist found',
        ]);
    }
    public function userArtistSave(Request $request)
    {
        $spotify_artists = $api->getArtists($request->artistIds);
        foreach($spotify_artists as $spotify_artist)
        {
            $user_artist = new UserArtist();
            $user_artist->spotify_artist_id = $spotify_artist->id;
            $user_artist->name = $spotify_artist->name;
            $user_artist->popularity = $spotify_artist->popularity;
            $user_artist->user_id = $request->userId;
            $user_artist->save();
        }
        return response()->json([
            'status' => 200,
            'message' => 'favourite artists saved',
        ],200);
    }
}
