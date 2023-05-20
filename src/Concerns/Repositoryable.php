<?php

namespace Mpietrucha\Repository\Concerns;

use Closure;
use Exception;
use Mpietrucha\Support\Rescue;
use Mpietrucha\Repository\Methods;
use Mpietrucha\Support\Concerns\Singleton;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

trait Repositoryable
{
    use Singleton;

    protected function withRepository(RepositoryInterface $repository): self
    {
        if ($this->getForward()) {
            throw new Exception('ForwardsCalls cannot be used before when using repository');
        }

        $this->forwardTo($repository)->forwardThenReturn(function () {
            if ($this->currentRepositoryIsStatic()) {
                return null;
            }

            return $this;
        });

        return $this;
    }

    public static function getStaticRepository(): ?RepositoryInterface
    {
        return self::singletonInstance()?->getForward();
    }

    public static function singletonResolving(string $method, array $arguments): void
    {
        self::getStaticRepository()->handlingStaticCall();
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->getForward();
    }

    public function repositoryValue(Closure $handler): array
    {
        if (! self::getStaticRepository()) {
            self::touchStaticRepository();
        }

        return [value($handler, $this->getRepository()->allowRepositoryRead()), value($handler, self::getStaticRepository()->allowRepositoryRead())];
    }

    public function repositoryStaticMethods(string|array $methods): self
    {
        collect($methods)->each(fn (string $method) => $this->repositoryStaticMethod($method));

        return $this;
    }

    public function repositoryStaticMethod(string $method): self
    {
        return $this->repositoryMethod($method, true);
    }

    public function repositoryInstanceMethods(string|array $methods): self
    {
        collect($methods)->each(fn (string $method) => $this->repositoryInstanceMethod($method));

        return $this;
    }

    public function repositoryInstanceMethod(string $method): self
    {
        return $this->repositoryMethod($method, false);
    }

    protected function repositoryMethod(string $method, bool $static): self
    {
        $this->forwardMethodTap($method, function () use ($method, $static) {
            if ($this->currentRepositoryIsStatic() === $static) {
                return;
            }

            $this->forwardAllowedMethods($method);
        });

        return $this;
    }

    protected function currentRepositoryIsStatic(): bool
    {
        return $this->getForward() === self::getStaticRepository();
    }
}
