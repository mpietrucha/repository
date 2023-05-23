<?php

namespace Mpietrucha\Repository\Contracts;

use Closure;

interface RepositoryInterface
{
    public function allowRepositoryRead(bool $read = true): self;

    public function whenNeedsRepositoryable(Closure $repositoryable): void;

    public function getRepositoryable(): ?object;

    public function withReposioryStaticCall(): void;

    public function handlingRepositoryStaticCall(): bool;

    public function assertRepositoryStaticCall(string $method): void;
}
