<?php
namespace ver;

use Veranda;
use Veranda\Abort;
use Veranda\Protocol;

const UNIT = ':unit';

function debug(): bool
{
    return config('app.debug');
}

function xheaders(bool $refresh = false): Protocol\XHeaders
{
    $container = \app();
    $refresh && $container->forgetInstance('luclin.xheaders');
    return $container['luclin.xheaders'];
}

function padding(string $template, array $vars): ?string
{
    // :abc + {{abc}} 相对单纯方案
    $result = preg_replace_callback(
        '/:([0-9]+)|:([A-Za-z_\@\$\.\-\~]+[0-9]*)|(\{\{)([A-Za-z0-9_\@\$\.\-\~\#\&]+)(\}\})/',
        function($matches) use ($vars) {
            $key = $matches[4] ?? $matches[2] ?? $matches[1];
            return $vars[$key] ?? $matches[0];
        }, $template);

    return $result;
}

function raise($error, array $extra = [], \Throwable $previous = null): Abort
{
    $params = [];
    if (is_string($error))
    {
        if (!($conf = config("abort.$error")) && !is_array($conf))
        {
            throw new \UnexpectedValueException("Raise error config is not found.");
        }
        $num =$conf['num'] ?? $conf;
        $msg = isset($conf['msg']) ? padding($conf['msg'], $extra) : __("abort.$error", $extra);
        $exc = $conf['exc'] ?? \LogicException::class;
        $error = new $exc($msg, $num, $previous);
        $params[] = $error;
        $params[] = $extra;
        isset($conf['lvl']) && $params[] = $conf['lvl'];
    }else{
        $params[] = $error;
        $params[] = $extra;
    }
    $abort = new Abort(...$params);

    return $abort;
}

function flex(...$arguments): Veranda\Flex {
    return new Veranda\Flex(...$arguments);
}

function context(...$arguments): Veranda\Context {
    return new Veranda\Context(...$arguments);
}

function _($primary): Veranda\Pipe {
    return new Veranda\Pipe($primary, new Veranda\Utils());
}

function head(iterable $data, $noTail = false): array {
    if ($noTail) {
        $head = $data[0] ?? null;
        $tail = null;
    } else {
        $head = array_shift($data);
        $tail = $data;
    }
    return [$head, $tail];
}

function tail(iterable $data, $noHead = true): array {
    if ($noHead) {
        $tail = $data[count($data) - 1] ?? null;
        $head = null;
    } else {
        $tail = array_pop($data);
        $head = $data;
    }
    return [$tail, $head];
}

