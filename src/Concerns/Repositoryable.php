<?php

namespace Mpietrucha\Repository\Concerns;

use Mpietrucha\Support\Concerns\Singleton;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

trait Repositoryable
{
    use Singleton;

    protected function withRepository(RepositoryInterface $repository): void
    {
        $this->forwardTo($repository)->forwardThenReturnThis();
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->getForward();
    }

    public static function singletonResolving(string $method, array $arguments): void
    {
        self::singletonInstance()->handlingStaticCall();
    }
}