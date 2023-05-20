<?php

namespace Mpietrucha\Repository;

use Exception;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    protected bool $handlingStaticCall = false;

    public function withStaticCall(): void
    {
        $this->handlingStaticCall = true;
    }

    public function handlingStaticCall(): bool
    {
        return $this->handlingStaticCall;
    }

    public function assertStaticCall(string $method): void
    {
        throw_unless($this->handlingStaticCall(), new Exception("Method $method is not allowed in static context"));
    }
}
