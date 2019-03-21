<?php

namespace App\BotCommands;

use App\TelegramAPI;
use App\TelegramMessage;

class HelpCommand
{
    protected $message;

    protected $telegram;

    protected $commands = [
        '/addrss {url}' => 'Add new rss to your list',
        '/pull' => 'Pull one item from your feed',
        '/pull 3' => 'Pull 3 (or any other number) items from your feed'
    ];

    public function handle(TelegramMessage $message)
    {
        $this->telegram = new TelegramAPI();

        $this->telegram->sendMessage($message->chat_id, $this->getFormattedMessage());
    }

    protected function getFormattedMessage()
    {
        $text = "Currently next commands are available:\n";

        foreach ($this->commands as $command => $description) {
            $text .= "<b>{$command}</b>: {$description}\n";
        }
    }
}
