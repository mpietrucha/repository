<?php

namespace Mpietrucha\Repository;

use Exception;

abstract class Repository
{
    protected bool $handlingStaticCall = false;

    public function handlingStaticCall(): void
    {
        $this->handlingStaticCall = true;
    }

    public function assertStaticCall(string $method): void
    {
        throw_unless($this->handlingStaticCall, new Exception("Method $method is not allowed in static context"));
    }
}
