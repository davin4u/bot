<?php

namespace App\BotCommands;

use App\FeedContent;
use App\TelegramAPI;
use App\TelegramMessage;
use App\UserFeed;
use Illuminate\Support\Facades\DB;

class PullCommand
{
    protected $message;

    protected $telegram;

    public function handle(TelegramMessage $message)
    {
        $this->telegram = new TelegramAPI();

        $viewed = DB::table('feed_content_status')
                        ->select("feed_content_id")
                        ->where('user_id', $message->user_id)
                        ->where('viewed', 1)
                        ->get()->pluck("feed_content_id")->toArray();

        $feeds = UserFeed::where('user_id', $message->user_id)->get()->pluck("feed_id")->toArray();

        $amount = (int) trim(str_replace('/pull', '', $message->text));

        if (! $amount) {
            $amount = 1;
        }

        $content = FeedContent::whereIn('feed_id', $feeds)
                                ->whereNotIn('id', $viewed)
                                ->offset(0)
                                ->limit($amount);

        if ($content) {
            foreach ($content as $item) {
                $item->setViewedBy($message->user_id);

                $this->telegram->sendMessage($message->chat_id, $item->getFormattedMessage());
            }
        }
        else {
            $this->telegram->sendMessage($message->chat_id, "Nothing new...");
        }
    }
}