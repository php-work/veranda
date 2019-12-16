<?php

namespace Veranda\Features;

/**
 * TODO: 这个方法暂时不靠谱，需要改进
 */
trait Pipable {
    public function pipe($primary = null): \Veranda\Pipe {
        return new \Veranda\Pipe($primary, $this);
    }
}
