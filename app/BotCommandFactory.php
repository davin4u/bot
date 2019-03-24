<?php

namespace App;

class BotCommandFactory
{
    protected static $commandsNamespace = 'App\\BotCommands';

    public static function get($name)
    {
        $command = static::$commandsNamespace . '\\' . ucfirst($name) . 'Command';

        if (class_exists($command)) {
            return app()->make($command);
        }

        return null;
    }
}
