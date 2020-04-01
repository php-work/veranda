<?php

namespace Veranda;

use Veranda\Contracts;
use Illuminate\Support\Str;

/**
 */
class Loader
{
    use SingletonNamedTrait;

    protected $cache = [];

    protected $registers = [];

    public function register(...$namespaces): self
    {
        if (is_callable($namespaces[0]))
        {
            $builder = array_shift($namespaces);
        } else {
            $builder = true;
        }
        foreach ($namespaces as $namespace)
        {
            $this->registers[$namespace] = $builder;
        }

        return $this;
    }

    public function class(string $name): ?array
    {
        if (!array_key_exists($name, $this->cache))
        {
            $this->cache[$name] = null;

            $class  = Str::studly($name);
            foreach ($this->registers as $namespace => $builder)
            {
                $fullName = "$namespace\\$class";
                if (class_exists($fullName))
                {
                    $this->cache[$name] = [$fullName, $builder];
                    break;
                }
            }
        }

        return $this->cache[$name];
    }

    public function make(string $name, ...$arguments)
    {
        [$class, $builder] = $this->class($name) ?: [null, null];
        if (!$class)
        {
            return null;
        }

        if (is_callable($builder))
        {
            return $builder($class, ...$arguments);
        }
        if (method_exists($class, 'instance'))
        {
            return $class::instance(...$arguments);
        }
        if (method_exists($class, 'new'))
        {
            return $class::new(...$arguments);
        }

        return new $class(...$arguments);
    }
}
