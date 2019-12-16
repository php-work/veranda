<?php
namespace ver;

use Illuminate\Support\Collection;

class variant
{
    private $arguments = [];

    public function __construct(...$arguments) {
        $this->arguments = $arguments;
    }

    public function __invoke(): Collection {
        return collect($this->arguments);
    }
}
