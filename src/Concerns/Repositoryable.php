<?php

namespace Mpietrucha\Repository\Concerns;

use Mpietrucha\Support\Types;
use Mpietrucha\Exception\RuntimeException;
use Mpietrucha\Support\Concerns\Singleton;
use Mpietrucha\Support\Concerns\ForwardsCalls;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

trait Repositoryable
{
    use Singleton;

    public function withRepository(RepositoryInterface $repository): self
    {
        throw_if($this->getForward(), new RuntimeException(
            [ForwardsCalls::class], 'cannot be used before using repository'
        ));

        $this->forwardTo($repository)->forwardThenReturn(function () {
            if ($this->getRepository()->isStatic()) {
                return null;
            }

            return $this;
        });

        $repository->whenNeedsRepositoryable(fn () => $this);

        $repository->whenNeedsStatic(fn () => self::singletonInstance()?->getForward());

        return $this;
    }

    public function withRepositoryStaticMethods(string|array $methods): self
    {
        return $this->withRepositoryMethods($methods, true);
    }

    public function withRepositoryStaticMethod(string $method): self
    {
        return $this->withRepositoryMethod($method, true);
    }

    public function withRepositoryInstanceMethods(string|array $methods): self
    {
        return $this->withRepositoryMethods($methods, false);
    }

    public function withRepositoryInstanceMethod(string $method): self
    {
        return $this->withRepositoryMethod($method, false);
    }

    public function withRepositoryMethods(array|string $methods, ?bool $static = null): self
    {
        collect($methods)->each(fn (string $method) => $this->withRepositoryMethod($method, $static));

        return $this;
    }

    public function withRepositoryMethod(string $method, ?bool $static = null): self
    {
        if (Types::null($static)) {
            return $this->forwardAllowedMethods($method);
        }

        $this->forwardMethodTap($method, function () use ($method, $static) {
            if ($this->getRepository()->static() === $static) {
                return;
            }

            $this->forwardAllowedMethods($method);
        });
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->getForward();
    }

    public static function singletonCalling(string $method, array $arguments): void
    {
        self::singletonInstance()->getForward()->isStatic();
    }
}
