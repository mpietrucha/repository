<?php

namespace Mpietrucha\Repository\Concerns;

use Exception;
use Mpietrucha\Support\Concerns\Singleton;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

trait Repositoryable
{
    use Singleton;

    protected function withRepository(RepositoryInterface $repository): void
    {
        if ($this->getForward()) {
            throw new Exception('ForwardsCallas cannot be used when using repository');
        }

        $this->forwardTo($repository)->forwardThenReturn(function () {
            if ($this->getForward() === self::getStaticRepository()) {
                return null;
            }

            return $this;
        });
    }

    public static function getStaticRepository(): ?RepositoryInterface
    {
        return self::singletonInstance();
    }

    public static function singletonResolving(string $method, array $arguments): void
    {
        self::getStaticRepository()->handlingStaticCall();
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
