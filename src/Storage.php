<?php

namespace Veranda;

use Veranda\Contracts;
use Veranda\Loader;
use Veranda\Meta;
use Illuminate\Support\Str;

class Storage extends Meta\Struct implements Contracts\Endpoint
{
    use Meta\Struct\UriableTrait;

    private $disk;
    private $action;
    private $options;

    public function __construct(string $disk, string $action, array $options)
    {
        $this->disk     = $disk;
        $this->action   = $action;
        $this->options  = $options;
    }

    public function __invoke()
    {
        return $this->{$this->action}();
    }

    private function upload(): string
    {
        $conf = config("filesystems.disks.$this->disk");
        if (!$conf) \ver\raise('aborts.param_error');

        $domainName     = "App\\Http\\Outside\\Domains\\Storage\\".Str::studly($conf['driver']);
        if (!class_exists($domainName))
        {
            \ver\raise('aborts.storage_driver_not_exists', [
                'driver' => $this->disk,
            ]);
        }

        $domain         = $domainName::instance();
        $cdnFullpath    = $domain->upload($conf,
            $this->options['filepath'], $this->options['prefix'] ?? '');

        return $cdnFullpath;
    }

    public static function uriPath(): string
    {
        return 'storage';
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): self
    {
        [
            $disk,
            $action,
        ] = $arguments;

        return (new static($disk, $action, $options))->confirm();
    }
}
