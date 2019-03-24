<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    protected $fillable = ['user_id', 'model', 'model_id', 'value'];
}
