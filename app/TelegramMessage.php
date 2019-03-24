<?php

namespace App;

use App\BotCommands\PullCommand;
use App\Contracts\TelegramCommandInterface;
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
                if ($entity['type'] == 'bot_command') {
                    return substr($this->text, $entity['offset'] + 1, $entity['length'] - 1);
                }
            }
        }

        return false;
    }

    protected function processCommand($commandName)
    {
        /** @var TelegramCommand $commamd */
        $commamd = BotCommandFactory::get($commandName);

        if ($commamd !== null) {
            $commamd->setDependencies(['message' => $this])->handle();
        }
    }

    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'user_id', 'id');
    }
}
