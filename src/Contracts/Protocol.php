<?php
namespace Veranda\Contracts;

interface Protocol
{
    public function send($em, int $ec);
}
