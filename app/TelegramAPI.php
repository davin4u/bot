<?php

namespace App;

use GuzzleHttp\Client;

class TelegramAPI
{
    protected $base_endpoint = "https://api.telegram.org/bot";

    protected $bot_username = null;

    protected $bot_api_key = null;

    protected $client = null;

    public function __construct($bot_username, $bot_api_key)
    {
        $this->bot_username = $bot_username;
        $this->bot_api_key = $bot_api_key;

        $this->client = new Client();
    }

    public function getUpdates()
    {
        $response = $this->client->get($this->base_endpoint . $this->bot_api_key . '/getUpdates');

        return [
            'status' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents()
        ];
    }
}
