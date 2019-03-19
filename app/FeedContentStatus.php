<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedContentStatus extends Model
{
    protected $table = 'feed_content_status';

    protected $fillable = ['feed_content_id', 'user_id', 'viewed'];
}
