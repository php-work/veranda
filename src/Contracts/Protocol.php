<?php
namespace Veranda\Contracts;

use Veranda\Abort;

interface Protocol
{
    public function abort(Abort $abort);
}