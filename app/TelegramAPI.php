<?php

namespace App;

use GuzzleHttp\Client;
use Carbon\Carbon;

class TelegramAPI
{
    protected $base_endpoint = "https://api.telegram.org/bot";

    protected $bot_username = null;

    protected $bot_api_key = null;

    protected $client = null;

    protected $updates = [];

    public function __construct($bot_username, $bot_api_key)
    {
        $this->bot_username = $bot_username;
        $this->bot_api_key  = $bot_api_key;
        $this->client       = new Client();
    }

    public function getUpdates()
    {
        $offset = 0;

        $lastUpdate = TelegramUpdate::orderBy('id', 'desc')->first();

        if ($lastUpdate) {
            $offset = $lastUpdate->id + 1;
        }

        $response = $this->client->get($this->base_endpoint . $this->bot_api_key . '/getUpdates', [
            'offset' => $offset
        ]);

        if ($response->getStatusCode() != 200) {
            throw new \Exception("Telegram has responded with non 200 code.");
        }

        $body = json_decode($response->getBody()->getContents());

        if (! property_exists($body, "ok") || $body->ok !== true) {
            throw new \Exception("Something wrong.");
        }

        $this->updates = $body->result;

        return $this;
    }

    public function processUpdates()
    {
        $prepared = collect([]);

        if (count($this->updates) > 0) {
            foreach ($this->updates as $update) {
                $telegramUpdate = TelegramUpdate::where('id', $update->update_id)->first();

                if ($telegramUpdate) {
                    continue;
                }

                if (property_exists($update, "message")) {
                    $message = $update->message;
                    $user = null;
                    $chat = null;



                    if (property_exists($message, "from")) {
                        $from = $message->from;

                        $user = TelegramUser::where('id', $from->id)->first();

                        if (! $user) {
                            $user = TelegramUser::create([
                                'id' => $from->id,
                                'is_bot' => $from->is_bot,
                                'first_name' => $from->first_name,
                                'last_name' => $from->last_name,
                                'username' => $from->username,
                                'language_code' => $from->language_code
                            ]);
                        }
                    }

                    if (property_exists($message, "chat")) {
                        $chat = TelegramChat::where('id', $message->chat->id)->first();

                        if (! $chat) {
                            $chat = TelegramChat::create([
                                'id' => $message->chat->id,
                                'type' => $message->chat->type,
                                'title' => $message->chat->type == 'private' ? NULL : $message->chat->title,
                                'username' => $message->chat->type != 'private' ? NULL : $message->chat->username
                            ]);
                        }
                    }

                    if ($user && $chat) {
                        $user_chat = UserChat::where('user_id', $user->id)->where('chat_id', $chat->id)->first();

                        if (! $user_chat) {
                            $user_chat = UserChat::create([
                                'user_id' => $user->id,
                                'chat_id' => $chat->id
                            ]);
                        }

                        $telegramMessage = TelegramMessage::where('id', $message->message_id)->where('chat_id', $chat->id)->first();

                        if (! $telegramMessage) {
                            TelegramMessage::create([
                                'id' => $message->message_id,
                                'chat_id' => $chat->id,
                                'user_id' => $user->id,
                                'date' => Carbon::createFromTimestamp($message->date)->toDateTimeString(),
                                'text' => $message->text
                            ]);

                            $telegramMessage = TelegramMessage::where('id', $message->message_id)->first();
                        }

                        $telegramUpdate = TelegramUpdate::create([
                            'id' => $update->update_id,
                            'chat_id' => $chat->id,
                            'message_id' => $telegramMessage->id
                        ]);

                        $prepared->push($telegramUpdate);
                    }
                }
            }
        }

        return $prepared;
    }
}
