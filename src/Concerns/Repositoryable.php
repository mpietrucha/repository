<?php

namespace Mpietrucha\Repository\Concerns;

use Mpietrucha\Support\Concerns\Singleton;

trait Repositoryable
{
    use Singleton;

    protected function withRepository(RepositoryInterface $repository): void
    {
        $this->forwardTo($respository);
    }

    public static function singletonResolving(string $method, array $arguments): void
    {
        self::singletonInstance()->handlingStaticCall();
    }
}
