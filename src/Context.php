<?php

namespace Veranda;

use Veranda\Meta;

class Context extends Meta\Collection implements Contracts\Context
{
    public function get($key, $default = null)
    {
        $result = parent::get($key, $default);
        if ($result === null)
        {
            try {
                $result = resolve("context.$key");
            } catch (\ReflectionException $exc) {
                // do nothing..
                $result = null;
            }
        }
        return $result;
    }
}
