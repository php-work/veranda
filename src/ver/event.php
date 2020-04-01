<?php

namespace ver;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class event
{
    public static $alias = [];

    public static function __callStatic(string $name, array $arguments)
    {
        $class = self::$alias[$name]."\\".Str::studly(array_shift($arguments));

        $event = new $class(...$arguments);
        \event($event);
    }
}