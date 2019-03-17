<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Longman\TelegramBot\Telegram;
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

        //$this->telegram = new Telegram(env('BOT_API_KEY'), env('BOT_USERNAME'));

        $this->telegram = new TelegramAPI(env('BOT_USERNAME'), env('BOT_API_KEY'));
    }

    public function update()
    {
        dd($this->telegram->getUpdates());

        /*
        try {
            $this->telegram->enableMySql($this->credentials);

            $response = $this->telegram->handleGetUpdates();

            dd($response);
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            return response(['error' => $e->getMessage()]);
        }

        return response(['success' => true]);*/
    }
}
