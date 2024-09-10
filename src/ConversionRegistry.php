<?php

namespace Turahe\Media;

use Turahe\Media\Exceptions\InvalidConversion;

class ConversionRegistry
{
    /** @var array */
    protected $conversions = [];

    /**
     * Get all the registered conversions.
     */
    public function all(): array
    {
        return $this->conversions;
    }

    /**
     * Register a new conversion.
     *
     * @return void
     */
    public function register(string $name, callable $conversion)
    {
        $this->conversions[$name] = $conversion;
    }

    /**
     * Get the conversion with the specified name.
     *
     * @return mixed
     *
     * @throws InvalidConversion
     */
    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw InvalidConversion::doesNotExist($name);
        }

        return $this->conversions[$name];
    }

    /**
     * Determine if a conversion with the specified name exists.
     */
    public function exists(string $name): bool
    {
        return isset($this->conversions[$name]);
    }
}
