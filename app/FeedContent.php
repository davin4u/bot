<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedContent extends Model
{
    protected $table = 'feed_content';

    protected $fillable = ['feed_id', 'guid', 'title', 'link', 'publish_date', 'description', 'content'];

    public static function checkIfExists($data)
    {
        if (! empty($data['guid'])) {
            return (bool) static::where('guid', $data['guid'])->count();
        }

        if (! empty($data['link'])) {
            return (bool) static::where('link', $data['link'])->count();
        }

        return false;
    }

    public function setViewedBy($user_id)
    {
        if ($user_id) {
            FeedContentStatus::create([
                'user_id' => $user_id,
                'feed_content_id' => $this->id,
                'viewed' => 1
            ]);
        }
    }

    public function getFormattedMessage()
    {
        return '<b>' . $this->title . '</b>'
                . "%0A" . $this->description . "%0A"
                . '<a href="' . $this->link . '">' . $this->link . '</a>';
    }
}
