<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFeed extends Model
{
    protected $table = 'user_feeds';

    protected $fillable = ['user_id', 'feed_id'];
}
