<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = [
        'email', 'token'
    ];
    protected $primaryKey = 'email';
    const UPDATED_AT = null;
    public $incrementing = false;
}
