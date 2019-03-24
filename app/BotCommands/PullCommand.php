<?php

namespace App\BotCommands;

use App\Contracts\TelegramCommandInterface;
use App\FeedContent;
use App\TelegramAPI;
use App\TelegramCommand;
use App\TelegramMessage;
use App\UserFeed;
use Illuminate\Support\Facades\DB;

class PullCommand extends TelegramCommand implements TelegramCommandInterface
{
    /**
     * @var TelegramMessage
     */
    protected $message;

    public function handle()
    {
        $viewed = DB::table('feed_content_status')
                        ->select("feed_content_id")
                        ->where('user_id', $this->message->user_id)
                        ->where('viewed', 1)
                        ->get()->pluck("feed_content_id")->toArray();

        $feeds = UserFeed::where('user_id', $this->message->user_id)->get()->pluck("feed_id")->toArray();

        $amount = (int) trim(str_replace('/pull', '', $this->message->text));

        if (! $amount && $amount > 5) {
            $amount = 1;
        }

        $content = FeedContent::whereIn('feed_id', $feeds)
                                ->whereNotIn('id', $viewed)
                                ->offset(0)
                                ->limit($amount)
                                ->get();

        if ($content) {
            foreach ($content as $item) {
                $item->setViewedBy($this->message->user_id);

                $this->telegram->sendMessageWithLikeButtons($this->message->chat_id, $item->getFormattedMessage(), 'FeedContent:' . $item->id);
            }
        }
        else {
            $this->telegram->sendMessage($this->message->chat_id, "Nothing new...");
        }
    }
}