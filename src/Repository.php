<?php

namespace Mpietrucha\Repository;

use Closure;
use Illuminate\Support\Collection;
use Mpietrucha\Exception\BadMethodCallException;
use Mpietrucha\Exception\RuntimeException;
use Mpietrucha\Repository\Contracts\RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    protected bool $static = false;

    protected bool $readable = false;

    protected ?Closure $resolver = null;

    protected ?Closure $repositoryable = null;

    public function __get(string $property): mixed
    {
        throw_unless(property_exists($this, $property), new RuntimeException(
            'Property', [$property], 'not exists'
        ));

        throw_unless($this->readable, new RuntimeException(
            'Cannot read property', [$property]
        ));

        return $this->$property;
    }

    public function __call(string $method, array $arguments): mixed
    {
        throw_unless(method_exists($this, $method), new BadMethodCallException(
            'Method', [$method], 'not exists'
        ));

        throw_uless($this->readable, new RuntimeException(
            'Cannot call method', [$method]
        ));

        return $this->$method(...$arguments);
    }

    public function whenNeedsRepositoryable(Closure $repositoryable): void
    {
        $this->repositoryable = $repositoryable;
    }

    public function whenNeedsStatic(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function static(): bool
    {
        return $this->static;
    }

    public function readable(bool $mode = true): self
    {
        $this->readable = $mode;

        return $this;
    }

    public function isStatic(): void
    {
        $this->static = true;
    }

    public function value(Closure $handler, ?Closure $default = null): mixed
    {
        $response = $this->collection($handler)->filter()->first();

        if (! $response && $default) {
            value($default);

            return $this->value($handler);
        }

        return $response;
    }

    public function values(Closure $handler): array
    {
        $static = null;

        if (! $this->isStatic() && $static = value($this->resolver)) {
            $static = value($handler, $static);
        }

        return [value($handler, $this), $static];
    }

    public function collection(Closure $handler): Collection
    {
        return collect($this->values($handler));
    }
}
