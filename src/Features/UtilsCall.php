<?php

namespace Veranda\Features;

use Veranda\Utils;

trait UtilsCall {
    public function __call(string $name, array $arguments) {
        $utils = new Utils();
        return $utils->$name($this, ...$arguments);
    }
}
