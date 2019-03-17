<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramChat extends Model
{
    protected $table = 'chat';

    protected $fillable = ['id', 'type', 'title', 'username'];
}
