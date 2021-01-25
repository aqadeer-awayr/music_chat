<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rennokki\Larafy\Exceptions\SpotifyAPIException;

class TokenManagerController extends Controller
{
    public function swap(Request $request)
    {
        // \Log::info($request->code);
        $code = $request->code;
        // \Log::info($response = json_decode($request->getBody()));
        $client = new \GuzzleHttp\Client();

        try {
            $requestData = $client->request('POST', 'https://accounts.spotify.com/api/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accepts' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode('a528071b06864ed0ab94cafae565df99' . ':' . '1dd78503cd9a441c88d64b7913bc4acc'),
                ],
                'form_params' => [
                    "grant_type" => "authorization_code",
                    "redirect_uri" => 'VIB://spotify-login-callback',
                    "code" => $code
                ],
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new SpotifyAPIException('Spotify API cannot generate the User Credentials Token.', json_decode($e->getResponse()->getBody()->getContents()));
        }

        $response = $requestData->getBody();
        // \Log::info($response);
        return $response;
    }
}
