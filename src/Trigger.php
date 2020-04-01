<?php

namespace Veranda;

use Veranda\Contracts;
use Veranda\Loader;
use Veranda\Meta;
use Illuminate\Support\Str;

class Trigger extends Meta\Struct implements Contracts\Endpoint
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

    public function raise()
    {
        $class = "App\\Http\\".Str::studly($this->module).
            "\\Triggers\\".Str::studly($this->name);

        return (new $class)->handle($this->all());
    }

    public static function uriPath(): string
    {
        return 'trigger';
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): self
    {
        return (new static($arguments, $options, $context))->confirm();
    }
}
