<?php

namespace Mpietrucha\Repository;

use Exception;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    protected bool $repositoryReading = false;

    protected bool $handlingRepositoryStaticCall = false;

    public function __get(string $property): mixed
    {
        if (! property_exists($this, $property)) {
            throw new Exception("Cannot read property `$property`");
        }

        if (! $this->repositoryReading) {
            throw new Exception("Cannot read property `$property` while reading is disabled");
        }

        return $this->allowRepositoryRead(false)->$property;
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (! method_exists($this, $method)) {
            throw new Exception("Call to undefined method `$method`");
        }

        if (! $this->repositoryReading) {
            throw new Exception("Cannot call method `$method` while reading is disabled");
        }

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
        throw_unless($this->handlingRepositoryStaticCall(), new Exception("Method `$method` is not allowed in static context"));
    }
}
