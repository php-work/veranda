<?php

namespace Veranda\Interaop;

use Veranda\Flex;

class Request extends Flex
{

    private $raw;

    public function __construct(array $request = null) {
        $request && $this->assign($request);
    }

    public function setRaw(object $raw): void {
        $this->raw = $raw;
    }

    public function raw(): object {
        return $this->raw;
    }

    protected static function defaults(): array {
        return [];
    }

    protected static function validate(): array {
        return [];
    }

    public function resolve(...$tails) {
        $this[] = function(array $request) {
            return \ver\_($request)->defaults(static::defaults(), $this)();
        };
        parent::resolve(...$tails);
    }
}