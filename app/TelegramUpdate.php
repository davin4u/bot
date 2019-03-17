<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramUpdate extends Model
{
    protected $table = 'telegram_update';

    protected $fillable = ['id', 'chat_id', 'message_id'];
}
