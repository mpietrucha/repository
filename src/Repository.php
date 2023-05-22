<?php

namespace Mpietrucha\Repository;

use Mpietrucha\Exception\RuntimeException;
use Mpietrucha\Exception\BadFunctionCallException;
use Mpietrucha\Exception\InvalidArgumentException;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    protected bool $repositoryReading = false;

    protected bool $handlingRepositoryStaticCall = false;

    public function __get(string $property): mixed
    {
        throw_unless(property_exists($this, $property), new InvalidArgumentException(
            'Cannot read property', [$property]
        ));

        throw_unless($this->repositoryReading, new RuntimeException(
            'Cannot read property', [$property], 'while reading is disabled'
        ));

        return $this->allowRepositoryRead(false)->$property;
    }

    public function __call(string $method, array $arguments): mixed
    {
        throw_unless(method_exists($this, $method), new BadFunctionCallException(
            'Call to undefined method', [$method]
        ));

        throw_unless($this->repositoryReading, new RuntimeException(
            'Cannot read property', [$property], 'while reading is disabled'
        ));

        return $this->allowRepositoryRead(false)->$method(...$arguments);
    }

    public function allowRepositoryRead(bool $read = true): self
    {
        $this->repositoryReading = $read;;

        return $this;
    }

    public function withReposioryStaticCall(): void
    {
        $this->handlingRepositoryStaticCall = true;
    }

    public function handlingRepositoryStaticCall(): bool
    {
        return $this->handlingRepositoryStaticCall;
    }

    public function assertRepositoryStaticCall(string $method): void
    {
        throw_unless($this->handlingRepositoryStaticCall(), new RuntimeException(
            'Method', [$method], 'is not allowed in static context'
        ));
    }
}
