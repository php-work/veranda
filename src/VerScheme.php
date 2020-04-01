<?php

namespace Veranda;

use Veranda\Contracts\Context;
use Veranda\Luri\{
    Scheme,
    Preset
};

class VerScheme extends Scheme
{
    protected static function _nexts(): array
    {
        return [
        ] + parent::_nexts();
    }

    public function construct()
    {

    }
}
