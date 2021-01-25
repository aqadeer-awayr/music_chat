<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'email', 'password', 'profile_image', 'is_gmail_login', 'is_facebook_login', 'bio'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $appends = ['followers_count', 'followings_count'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function searchUsers($request = '')
    {
        // this way will fire up speed of the query
        $query = self::query()->with('userGenres');

        // search for multiple columns..
        if (isset($request['query']) && $request['query']) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request['query'] . '%');
                $q->orWhere('email', $request['query']);
            });
        }
        // search for relational table columns put here variable name place of 'q' and uncomment below lines

        // if (isset($request['q']) && $request['q']) {
        //     $query->whereHas('userGenres', function ($que) use ($request) {
        //         $que->where('genre', 'like', '%' . $request['q'] . '%');
        //     });
        // }


        // order By..
        if (isset($request['sort']) && $request['sort']) {
            $query->orderBy('id', $request['sort']);
        } else {
            $query->orderBy('id', 'DESC');
        }

        // feel free to add any query filter as much as you want...

        // if need pagination please uncomment below line and comment $query->with('userGenres')->get(); this line

        // return $query->with('userGenres')->paginate($request['paginate'] ?? 10);


        return $query->get();
    }
    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new MyCustomResetPasswordNotification($token)); <--- remove this, use Mail instead like below

        $request = [
            $this->email
        ];

        \Mail::send('email.reset-password', [
            'fullname'      => $this->fullname,
            'reset_url'     => route('user.password.reset', ['token' => $token, 'email' => $this->email]),
        ], function ($message) use ($request) {
            $message->subject('Reset Password Request');
            $message->to($request[0]);
        });
        return response()->json(compact('token'), 201);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function userArtists()
    {
        return $this->hasMany(UserArtist::class, 'user_id', 'id');
    }
    public function followings()
    {
        return $this->hasMany(Following::class, 'user_id', 'id');
    }
    public function followers()
    {
        return $this->hasMany(Following::class, 'following_user_id', 'id');
    }
    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }
    public function getFollowingsCountAttribute()
    {
        return $this->followings()->count();
    }
    public function userGenres()
    {
        return $this->hasMany(UserGenre::class, 'user_id', 'id');
    }
    public function is_follower($loggedInUserId)
    {
        return self::where('id', $loggedInUserId)->first()->followings->where('following_user_id', $this->id)->count();
    }
}
