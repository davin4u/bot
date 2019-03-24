<?php

namespace App;

class TelegramCommandRelationProvider
{
    protected $namespaces = [];

    public function __construct()
    {
        $this->namespaces = config('bot.callback_query.relations');
    }

    public function find($relation)
    {
        $parts = explode(":", $relation);

        if (! empty($parts[0]) && ! empty($parts[1])) {
            $model = $this->recognizeModel($parts[0]);

            if ($model) {
                return $model->find($parts[1]);
            }
        }

        return null;
    }

    protected function recognizeModel($name)
    {
        foreach ($this->namespaces as $namespace) {
            if (class_exists($namespace . '\\' . ucfirst($name))) {
                return resolve($namespace . '\\' . ucfirst($name));
            }
        }

        return null;
    }
}
