<?php
namespace ver;

use Veranda\Contracts;

class protocol
{
    public static function __callStatic(string $name, array $arguments) {
        return \resolve(Contracts\Protocol::class)->$name(...$arguments);
    }
}