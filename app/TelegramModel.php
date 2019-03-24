<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramModel extends Model
{
    protected $telegram = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->telegram = new TelegramAPI();
    }
}