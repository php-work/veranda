<?php
namespace ver;

class protocol
{
    public static function __callStatic(string $name, array $arguments)
    {
        $protocol = new \Veranda\Protocol();
        return $protocol->{$name}(...$arguments);
    }
}
