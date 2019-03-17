<?php

namespace App\Http\Controllers;

use App\TelegramAPI;

class BotController extends Controller
{
    protected $credentials = [];

    protected $telegram = null;

    public function __construct()
    {
        $this->credentials = [
            'host'     => env('DB_HOST'),
            'port'     => 3306,
            'user'     => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
        ];

        $this->telegram = new TelegramAPI(env('BOT_USERNAME'), env('BOT_API_KEY'));
    }

    public function update()
    {
        $updates = $this->telegram->getUpdates()->processUpdates();

        if (count($updates) > 0) {
            $this->telegram->sendMessage('454018476', 'New Message was stored');
        }

        return response()->json(['success' => count($updates) > 0, 'updates' => $updates]);
    }
}
