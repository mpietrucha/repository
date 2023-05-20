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

    public static function singletonCalling(string $method, array $arguments): void
    {
        self::getStaticRepository()->withReposioryStaticCall();
    }

    public static function touchRepository(): void
    {
        if (self::getStaticRepository()) {
            return;
        }

        self::singletonCreate();
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->getForward();
    }

    public function repositoryValues(Closure $handler): array
    {
        self::touchRepository();

        return [value($handler, $this->getRepository()->allowRepositoryRead()), value($handler, self::getStaticRepository()->allowRepositoryRead())];
    }

    public function repositoryValue(Closure $handler): mixed
    {
        [$instance, $static] = $this->repositoryValues($handler);

        return $instance ?? $static;
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
        if (! self::getStaticRepository()) {
            return true;
        }

        return $this->getForward() === self::getStaticRepository();
    }
}
