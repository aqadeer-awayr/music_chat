<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use GuzzleHttp\Client;
use Rennokki\Larafy\Larafy;

class PlaylistController extends Controller
{
    public function store(Request $request)
    {
        $api = new Larafy();
        try {
            $spotify_user = $api->userProfileGet($request->token);
            // dd($spotify_user_playlists);
        } catch (\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
            return response()->json([
                'message' => $e->getAPIResponse()->error->message,
                'status' => $e->getAPIResponse()->error->status,
            ]);
        }
        try {
            $spotify_user_playlists = $api->createPlaylist($request->all(), $request->token, $spotify_user->id);
            // dd($spotify_user_playlists);
        } catch (\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
            dd($e);
            return response()->json([
                'message' => $e->getAPIResponse()->error->message,
                'status' => $e->getAPIResponse()->error->status,
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Playlist create successfully',
        ], 200);
        // $client = new \GuzzleHttp\Client();

        // try {
        //     $requestApi = $client->request('GET', 'https://api.spotify.com/v1/me', [
        //         'headers' => [
        //             'Content-Type' => 'application/x-www-form-urlencoded',
        //             'Accepts' => 'application/json',
        //             'Authorization' => 'Bearer '.$token,
        //         ]
        //     ]);
        // } catch (\GuzzleHttp\Exception\ClientException $e) {
        //     throw new SpotifyAPIException('Spotify API cannot generate the User Credentials Token.', json_decode($e->getResponse()->getBody()->getContents()));
        // }
        // $response = json_decode($request->getBody());
        // $spotify_user_id = $response->id;
        // $api = new Larafy();
        // try {
        //     $api->createPlaylist($request->all(), $token, $spotify_user_id);
        // } catch(\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
        //     return response()->json([
        //         'message' => $e->getAPIResponse()->error->message,
        //         'status' => $e->getAPIResponse()->error->status,
        //     ]);
        // }
        // return response()->json([
        //     'status' => 200,
        //     'message' => 'Playlist create successfully',
        // ], 200);
        // $api->withBearerToken($request->get('token'))->post()->request('https://api.spotify.com/v1/users/'.$userId.'/playlists', $request->all());
        // $client = new Client();
        // $headers = [
        //     'Authorization' => 'Bearer ' . $request->get('token'),
        //     'Accept'        => 'application/json',
        // ];
        // $response = $client->request('GET', 'https://api.spotify.com/v1/artists/'.'06HL4z0CvFAxyc27GXpf02', [
        //     'headers' => $headers,
        // ]);
        // $statusCode = $response->getStatusCode();
        // $body = $response->getBody()->getContents();

        // return $body;
    }
    public function spotifyUserPlaylists(Request $request)
    {

        $api = new Larafy();
        $api->getUserPlaylists($request->get('token'));
        // $api->getUserPlaylists('BQAXS9yTe8KRPVIPuE8RzDVhfBCDORt2hNBSxTK_eMcz-i0aquf7TMEZ3W2D1qtJEpMbn-P7sa2hK6T30dF0vuxaOnh60VV3a-uIxfIMP-JY-AKv0jSnMdOZ5f4rxqIE06D56CT1hMqq4XsvBqWVQj24tYD0ioDUmkVQw4EOX80qcKSqmO8QnjtLm9sJcye6MnrwLNCksomzJAASmiI8eJ75IYH7I-f5nd-uyh7PApJDRKpzuIeqajKaaJ6B9wXo18VEEQ');
        try {
            $searched_result = $api->getUserPlaylists($request->get('token'));
            // $searched_result = $api->getUserPlaylists('BQAXS9yTe8KRPVIPuE8RzDVhfBCDORt2hNBSxTK_eMcz-i0aquf7TMEZ3W2D1qtJEpMbn-P7sa2hK6T30dF0vuxaOnh60VV3a-uIxfIMP-JY-AKv0jSnMdOZ5f4rxqIE06D56CT1hMqq4XsvBqWVQj24tYD0ioDUmkVQw4EOX80qcKSqmO8QnjtLm9sJcye6MnrwLNCksomzJAASmiI8eJ75IYH7I-f5nd-uyh7PApJDRKpzuIeqajKaaJ6B9wXo18VEEQ');
        } catch (\Rennokki\Larafy\Exceptions\SpotifyAPIException $e) {
            return response()->json([
                'message' => $e->getAPIResponse()->error->message,
                'status' => $e->getAPIResponse()->error->status,
            ]);
        }
        return response()->json([
            'data' => $searched_result,
            'status' => 200,
            'message' => 'user playlists loaded successfully',
        ], 200);
    }
}
