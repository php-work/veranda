<?php

namespace Veranda;

class Utils {
    public function __call(string $name, array $arguments) {
        $class  = "Veranda\\Utils\\".ucfirst($name);
        $func   = new $class(...$arguments);
        return $func();
    }
}
