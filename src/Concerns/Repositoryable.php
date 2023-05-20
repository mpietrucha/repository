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

    public static function getStaticRepository(): ?RepositoryInterface
    {
        return self::singletonInstance()?->getRepository();
    }

    public static function singletonResolving(string $method, array $arguments): void
    {
        self::singletonInstance()->handlingStaticCall();
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->getForward();
    }

    public function readFromRepositories(Closure $handler): array
    {
        return [
            value($handler, $this->getRepository()),
            value($handler, self::getStaticRepository())
        ];
    }
}
