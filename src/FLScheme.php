<?php

namespace Veranda;

use Veranda\VerScheme;

class FLScheme extends VerScheme
{
    protected static function _nexts(): array
    {
        return [
            'trigger'   => Trigger::class,
            'event'     => Event::class,
            'storage'   => Storage::class,
        ] + parent::_nexts();
    }
}
