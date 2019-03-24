<?php

namespace App;

class CallbackQuery extends TelegramModel
{
    protected $table = 'callback_query';

    protected $fillable = ['id', 'user_id', 'chat_id', 'message_id', 'inline_message_id', 'data'];

    public function process()
    {
        if ($this->processed) {
            return;
        }
    }
}
