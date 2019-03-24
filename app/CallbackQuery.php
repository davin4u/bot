<?php

namespace App;

class CallbackQuery extends TelegramModel
{
    protected $table = 'callback_query';

    protected $fillable = ['id', 'user_id', 'chat_id', 'message_id', 'inline_message_id', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    public function process()
    {
        if ($this->processed) {
            return;
        }

        if (! empty($this->data['command'])) {
            /** @var TelegramCommand $command */
            $command = CallbackCommandFactory::get($this->data['command']);

            if ($command) {
                $command->setDependencies(['callback' => $this])->handle();

                $this->telegram->answerCallbackQuery($this);

                $this->processed = 1;

                $this->save();
            }
        }
    }
}
