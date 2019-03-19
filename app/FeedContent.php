<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedContent extends Model
{
    protected $table = 'feed_content';

    protected $fillable = ['feed_id', 'guid', 'title', 'link', 'publish_date', 'description', 'content'];
}
