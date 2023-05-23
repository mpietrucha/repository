<?php

namespace Mpietrucha\Repository\Contracts;

use Closure;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function whenNeedsRepositoryable(Closure $repositoryable): void;

    public function whenNeedsStatic(Closure $resolver): void;

    public function isStatic(): void;

    public function static(): bool;

    public function value(Closure $handler, ?Closure $default = null): mixed;

    public function values(Closure $handler): array;

    public function collection(Closure $handler): Collection;
}
