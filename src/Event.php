<?php

namespace Veranda;

use Veranda\Contracts;
use Veranda\Loader;
use Veranda\Meta;
use Illuminate\Support\Str;

class Event extends Meta\Struct implements Contracts\Endpoint
{
    use Meta\Struct\UriableTrait;

    private $module;
    private $name;

    public function __construct(array $arguments, array $options,
        Contracts\Context $context)
    {
        $this->module   = array_shift($arguments);
        $this->name     = implode('/', $arguments);
        $this->fill(array_merge($options, $context->all()));
    }

    public function raise(): void
    {
        $module = Str::studly($this->module);
        $name   = Str::studly($this->name);
        \ver\event::$module($name, $this->all());
    }

    public static function uriPath(): string
    {
        return 'event';
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): self
    {
        return (new static($arguments, $options, $context))->confirm();
    }
}
