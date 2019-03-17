<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $table = 'message';

    protected $fillable = ['id', 'chat_id', 'user_id', 'date', 'text'];

    public $timestamps = false;
}
