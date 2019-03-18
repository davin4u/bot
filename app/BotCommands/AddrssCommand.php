<?php

namespace App\BotCommands;

use App\TelegramMessage;
use App\UserFeed;
use App\Feed;

class AddrssCommand
{
    protected $message;

    public function handle(TelegramMessage $message)
    {
        $this->message = $message;

        if ($url = $this->extractUrl()) {
            $feed = Feed::where('url', $url)->first();

            if (! $feed) {
                $feed = Feed::create([
                    'url' => $url
                ]);
            }

            if ($this->message->user) {
                $relation = UserFeed::where('user_id', $this->message->user->id)->where('feed_id', $feed->id);

                if (! $relation) {
                    UserFeed::create([
                        'user_id' => $this->message->user->id,
                        'feed_id' => $feed->id
                    ]);
                }
            }
        }
    }

    protected function extractUrl()
    {
        if (! empty($this->message->entities)) {
            foreach ($this->message->entities as $entity) {
                if ($entity->type == 'url') {
                    return substr($this->message->text, $entity->offset, $entity->length);
                }
            }
        }

        return null;
    }
}
