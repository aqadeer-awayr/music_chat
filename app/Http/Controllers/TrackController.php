<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rennokki\Larafy\Larafy;

class TrackController extends Controller
{
    public function searchTrack(Request $request)
    {
        $api = new Larafy();
        $track_name = $request->get('track_name');
        try {
            $searched_result = $api->searchTracks($request->track_name);
        } catch(\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
            return response()->json([
                'message' => $e->getAPIResponse()->error->message,
                'status' => $e->getAPIResponse()->error->status,
            ]);
        }
        return response()->json([
            'data' =>  $searched_result->items,
            'status' => 200,
            'message' => 'Track searched successfully',
        ], 200);
    }
}
