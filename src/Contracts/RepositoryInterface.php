<?php

namespace Mpietrucha\Repository\Contracts;

interface RepositoryInterface
{
    public function allowRepositoryRead(bool $read = true): self;

    public function withReposioryStaticCall(): void;

    public function handlingRepositoryStaticCall(): bool;

    public function assertRepositoryStaticCall(string $method): void;
}
