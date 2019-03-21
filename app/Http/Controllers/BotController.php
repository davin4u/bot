<?php

namespace App\Http\Controllers;

use App\Feed;
use App\TelegramAPI;

class BotController extends Controller
{
    protected $telegram = null;

    public function __construct(TelegramAPI $telegram)
    {
        $this->telegram = $telegram;
    }

    public function update()
    {
        $updates = $this->telegram->getUpdates()->processUpdates();

        return response()->json(['success' => count($updates) > 0, 'updates' => $updates]);
    }

    public function updateFeedContents()
    {
        $feeds = Feed::all();

        foreach ($feeds as $feed) {
            $feed->updateFeedContent();
        }

        return response()->json(['success' => true]);
    }
}
