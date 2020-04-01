<?php

namespace Veranda\Meta\Collection;

/**
 *
 * @author andares
 */
trait ToBinTrait
{
    /**
     *
     * @return string
     */
    public function toBin(): string
    {
        return msgpack_pack($this->toArray());
    }
}
