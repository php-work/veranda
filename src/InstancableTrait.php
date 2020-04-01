<?php
namespace Veranda;

trait InstancableTrait
{
    public static function instance(...$arguments)
    {
        return new static(...$arguments);
    }
}