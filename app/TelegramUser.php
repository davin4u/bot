<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $table = 'user';

    protected $fillable = ['id', 'is_bot', 'first_name', 'last_name', 'username', 'language_code'];
}
