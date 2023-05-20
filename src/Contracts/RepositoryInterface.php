<?php

namespace Mpietrucha\Repository\Contracts;

interface RepositoryInterface
{
    public function withStaticCall(): void;

    public function handlingStaticCall(): bool;

    public function assertStaticCall(string $method): void;
}
