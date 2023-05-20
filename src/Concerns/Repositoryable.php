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

    protected static bool $staticRepositoryIsCurrentlyBooting = false;

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

    public static function singletonCreating(): void
    {
        self::$staticRepositoryIsCurrentlyBooting = true;
    }

    public static function touchRepository(): void
    {
        if (self::$staticRepositoryIsCurrentlyBooting) {
            return;
        }

        if (self::getStaticRepository()) {
            return;
        }

        self::$staticRepositoryIsCurrentlyBooting = true;

        self::singletonCreate();

        self::$staticRepositoryIsCurrentlyBooting = false;
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

    public function repositoryValue(Closure $handler, ?Closure $default = null): mixed
    {
        [$instance, $static] = $this->repositoryValues($handler);

        $response = $instance ?? $static;

        if (! $response && $default) {
            value($default);

            return $this->repositoryValue($handler);
        }

        return $response;
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
        self::touchRepository();

        if (! self::getStaticRepository()) {
            return self::$staticRepositoryIsCurrentlyBooting;
        }

        return $this->getForward() === self::getStaticRepository();
    }
}
