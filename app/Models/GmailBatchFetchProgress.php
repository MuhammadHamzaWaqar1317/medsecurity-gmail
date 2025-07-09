<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmailBatchFetchProgress extends Model
{
    //
    protected $fillable = [
        'uuid',
        'gmail_account_id',
        'batch_id'
    ];
}
