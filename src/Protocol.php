<?php
namespace Veranda;

use Veranda\Contracts;

class Protocol implements Contracts\Protocol
{
    public function send($em, int $ec)
    {
        return response()->json([
            'ec' => $ec,
            'em' => $em,
        ]);
    }

    public function success()
    {
        return $this->send('', 200);
    }

    public function fail($em = '')
    {
        return $this->send($em, 201);
    }

    public function result($em = '')
    {
        return $this->send($em, 200);
    }

    public function image(string $data, string $format = 'png')
    {
        return response($data, 200, [
            'content-type'  => "image/$format",
        ]);
    }
}
