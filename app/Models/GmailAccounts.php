<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailAccounts extends Model
{
    //
    protected $fillable = [
        'uuid',
        'user_id',
        'gmail_account',
        'access_token',
        'refresh_token',
        'expires_in',
        'scope'
    ];
}
