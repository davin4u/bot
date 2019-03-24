<?php

namespace App\BotCommands;

use App\Contracts\TelegramCommandInterface;
use App\TelegramCommand;
use App\TelegramMessage;

class HelpCommand extends TelegramCommand implements TelegramCommandInterface
{
    /**
     * @var TelegramMessage
     */
    protected $message;

    protected $commands = [
        '/addrss {url}' => 'Add new rss to your list',
        '/pull' => 'Pull one item from your feed',
        '/pull 3' => 'Pull 3 (or any other number) items from your feed'
    ];

    public function handle()
    {
        $this->telegram->sendMessage($this->message->chat_id, $this->getFormattedMessage());
    }

    protected function getFormattedMessage()
    {
        $text = "Currently next commands are available:\n";

        foreach ($this->commands as $command => $description) {
            $text .= "<b>{$command}</b>: {$description}\n";
        }

        return $text;
    }
}
