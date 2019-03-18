<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $table = 'message';

    protected $fillable = ['id', 'chat_id', 'user_id', 'date', 'text', 'entities'];

    protected $casts = [
        'entities' => 'array'
    ];

    public $timestamps = false;

    public function process()
    {
        if ($commandName = $this->checkIfCommand()) {
            $this->processCommand($commandName);
        }
    }

    protected function checkIfCommand()
    {
        if (! empty($this->entities)) {
            foreach ($this->entities as $entity) {
                if ($entity->type == 'bot_command') {
                    return substr($this->text, $entity->offset + 1, $entity->length - 1);
                }
            }
        }

        return false;
    }

    protected function processCommand($commandName)
    {
        $commamd = BotCommandFactory::get($commandName);

        if ($commamd !== null) {
            $commamd->handle($this);
        }
    }

    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'user_id', 'id');
    }
}
