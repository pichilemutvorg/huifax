<?php

namespace App\Display;

use App\Display\SlideTypes\SlideType;
use InvalidArgumentException;

/**
 * Slide types available in this installation, keyed by `SlideType::key()`.
 */
class SlideTypeRegistry
{
    /** @var array<string, SlideType> */
    private array $types = [];

    /**
     * @param  iterable<SlideType>  $types
     */
    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            if (isset($this->types[$type->key()])) {
                throw new InvalidArgumentException("Duplicate slide type key [{$type->key()}].");
            }

            $this->types[$type->key()] = $type;
        }
    }

    public function find(string $key): ?SlideType
    {
        return $this->types[$key] ?? null;
    }

    /**
     * @return array<string, SlideType>
     */
    public function all(): array
    {
        return $this->types;
    }
}
