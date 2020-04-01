<?php
namespace Veranda\Contracts;

interface Hook
{
    public function handle(array $context, array $vars = []);
}
