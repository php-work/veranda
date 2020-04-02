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
            'trigger'   => Trigger::class,
            'event'     => Event::class,
            'storage'   => Storage::class,
        ] + parent::_nexts();
    }

    public function construct()
    {

    }
}
