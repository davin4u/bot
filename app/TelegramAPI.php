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

    protected $withInlineKeyboard = false;

    public function __construct()
    {
        $this->bot_username = env('BOT_USERNAME');
        $this->bot_api_key  = env('BOT_API_KEY');
        $this->withInlineKeyboard = env('WITH_INLINE_KEYBOARD', false);
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

                $prepared->push($this->processTelegramUpdate($update));

                $this->processCallbackQuery($update);
            }
        }

        return $prepared;
    }

    protected function processUser($update) {
        $user = null;

        if (property_exists($update, "message") && property_exists($update->message, "from")) {
            $from = $update->message->from;

            $user = TelegramUser::where('id', $from->id)->first();

            if (! $user) {
                TelegramUser::create([
                    'id' => $from->id,
                    'is_bot' => $from->is_bot,
                    'first_name' => property_exists($from, "first_name") ? $from->first_name : '',
                    'last_name' => property_exists($from, "last_name") ? $from->last_name: '',
                    'username' => property_exists($from, "username") ? $from->username: '',
                    'language_code' => property_exists($from, "language_code") ? $from->language_code : ''
                ]);

                $user = TelegramUser::where('id', $from->id)->first();
            }
        }

        return $user;
    }

    protected function processChat($update) {
        $chat = null;

        if (property_exists($update, "message") && property_exists($update->message, "chat")) {
            $chat = TelegramChat::where('id', $update->message->chat->id)->first();

            if (! $chat) {
                TelegramChat::create([
                    'id' => $update->message->chat->id,
                    'type' => $update->message->chat->type,
                    'title' => $update->message->chat->type == 'private' ? NULL : $update->message->chat->title,
                    'username' => $update->message->chat->type != 'private' ? NULL : $update->message->chat->username
                ]);

                $chat = TelegramChat::where('id', $update->message->chat->id)->first();
            }
        }

        return $chat;
    }

    protected function processUserChatRelation($user = null, $chat = null) {
        if ($user && $chat) {
            $user_chat = UserChat::where('user_id', $user->id)->where('chat_id', $chat->id)->first();

            if (! $user_chat) {
                UserChat::create([
                    'user_id' => $user->id,
                    'chat_id' => $chat->id
                ]);
            }
        }
    }

    protected function processMessage($update, TelegramUser $user, TelegramCaht $chat) {
        $telegramMessage = null;

        if (property_exists($update, "message")) {
            $telegramMessage = TelegramMessage::where('id', $update->message->message_id)->where('chat_id', $chat->id)->first();

            if (! $telegramMessage && property_exists($update->message, "text")) {
                $entities = [];

                if (property_exists($update->message, "entities")) {
                    $entities = $update->message->entities;
                }

                TelegramMessage::create([
                    'id' => $update->message->message_id,
                    'chat_id' => $chat->id,
                    'user_id' => $user->id,
                    'date' => Carbon::createFromTimestamp($update->message->date)->toDateTimeString(),
                    'text' => $update->message->text,
                    'entities' => $entities
                ]);

                /** @var TelegramMessage $telegramMessage */
                $telegramMessage = TelegramMessage::where('id', $update->message->message_id)->first();

                $telegramMessage->process();
            }
        }

        return $telegramMessage;
    }

    protected function processTelegramUpdate($update)
    {
        $user = $this->processUser($update);
        $chat = $this->processChat($update);

        if ($user && $chat) {
            $this->processUserChatRelation($user, $chat);
            $telegramMessage = $this->processMessage($update, $user, $chat);

            if ($telegramMessage) {
                return TelegramUpdate::create([
                    'id' => $update->update_id,
                    'chat_id' => $chat->id,
                    'message_id' => $telegramMessage->id
                ]);
            }
        }

        return null;
    }

    protected function processCallbackQuery($update)
    {
        if (property_exists($update, "callback_query")) {
            $from = property_exists($update->callback_query, "from") ? $update->callback_query->from : null;
            $message = property_exists($update->callback_query, "message") ? $update->callback_query->message : null;
            $inline_message_id = property_exists($update->callback_query, "inline_message_id") ? $update->callback_query->inline_message_id : null;
            $callback_data = property_exists($update->callback_query, "data") ? $update->callback_query->data : null;

            $callback = CallbackQuery::where('id', $update->callback_query->id)->first();

            if (! $callback && $from) {
                $data = [
                    'id' => $update->callback_query->id,
                    'user_id' => $from->id,
                    'inline_message_id' => $inline_message_id,
                    'data' => $callback_data
                ];

                if ($message) {
                    $data['message_id'] = $message->message_id;

                    if (property_exists($message, "chat")) {
                        $data = array_merge($data, [
                            'chat_id' => $message->chat->id,
                        ]);
                    }
                }

                /** @var CallbackQuery $callback */
                $callback = CallbackQuery::create($data);

                if ($callback) {
                    $callback->process();
                }
            }
        }
    }

    public function sendMessage($chat_id, $text, $buttons = null)
    {
        return $this->client->post($this->base_endpoint . $this->bot_api_key . '/sendMessage', [
            'form_params' => [
                'chat_id' => $chat_id,
                'text' => strip_tags($text, '<a><b><i><code><pre>'),
                'parse_mode' => 'html',
                'reply_markup' => $buttons
            ]
        ]);
    }

    public function sendMessageWithLikeButtons($chat_id, $text)
    {
        $keyboard = null;

        if ($chat_id == env('OWN_CHAT_ID')) {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Like',
                            'callback_data' => '/like'
                        ],
                        [
                            'text' => 'Dislike',
                            'callback_data' => '/dislike'
                        ]
                    ]
                ]
            ];
        }

        $this->sendMessage($chat_id, $text, json_encode($keyboard));
    }

    public function answerCallbackQuery(CallbackQuery $callback, $text = '')
    {
        return $this->client->post($this->base_endpoint . $this->bot_api_key . '/answerCallbackQuery', [
            'callback_query_id' => $callback->id,
            'text' => $text
        ]);
    }
}
