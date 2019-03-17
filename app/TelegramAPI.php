<?php

namespace App;

use GuzzleHttp\Client;

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
        $response = $this->client->get($this->base_endpoint . $this->bot_api_key . '/getUpdates');

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
        if (count($this->updates) > 0) {
            foreach ($this->updates as $update) {
                $message = $update->message;

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
            }
        }

        return true;
    }
}
