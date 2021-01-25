<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PasswordReset;
use App\User;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Hash;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json(
                [
                    'status' => 404,
                    'message' => 'We cant find a user with that e-mail address.',
                ], 404);
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => mt_rand(100000, 999999),
             ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );
        // \Log::info('im here');
        return response()->json([
            'status' => 200,
            'message' => 'We have e-mailed your password reset token'
        ], 200);
    }
    
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset)
            return response()->json([
                'status' => 404,
                'message' => 'This password reset token is invalid.'
            ], 404);
        if (Carbon::parse($passwordReset->created_at)->addMinutes(15)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'status' => 404,
                'message' => 'This password reset token is expired.'
            ], 404);
        }
        return response()->json([
            'data' => $passwordReset,
            'status' => 200,
            'message' => 'password reset token matched',
        ],200);
    }
     /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            // 'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where([
            // ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset)
            return response()->json([
                'status' => 404,
                'message' => 'This password reset token not found',
            ], 404);
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return response()->json([
                'status' => 404,
                'message' => 'We cant find a user with that e-mail address.'
            ], 404);
        $user->password = Hash::make($request->password);
        $user->save();
        // $user->notify(new PasswordResetSuccess($passwordReset));
        $passwordReset->delete();
        return response()->json([
            'data' => $user,
            'status' => 200,
            'message' => 'password has been changed'
        ], 200);
    }
}
