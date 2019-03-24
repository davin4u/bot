<?php

namespace App\CallbackQueryCommands;

use App\Contracts\TelegramCommandInterface;
use App\TelegramCommand;
use App\UserVote;

class DislikeCommand extends TelegramCommand implements TelegramCommandInterface
{
    protected $callback;

    public function handle()
    {
        $relation = $this->extractRelation();

        if ($relation) {
            UserVote::create([
                'user_id' => $this->callback->user_id,
                'model' => $relation->getModelName(),
                'model_id' => $relation->id,
                'value' => 'dislike'
            ]);
        }
    }
}