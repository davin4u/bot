<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    protected $table = 'user_chat';

    protected $fillable = ['user_id', 'chat_id'];

    public $timestamps = false;
}
