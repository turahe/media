<?php

namespace Turahe\Media\Resize;

use InvalidArgumentException;

class ResizeOptions
{
    public const TYPES = ['fit', 'fill', 'fill-down', 'force', 'auto'];

    public function __construct(
        public int $width,
        public int $height = 0,
        public string $resizingType = 'fit',
        public bool $enlarge = false,
        public ?int $quality = null,
        public ?string $extension = null,
    ) {
        if (! in_array($this->resizingType, self::TYPES, true)) {
            throw new InvalidArgumentException("Unsupported resizing type [{$this->resizingType}].");
        }

        if ($this->quality !== null && ($this->quality < 0 || $this->quality > 100)) {
            throw new InvalidArgumentException('Image quality must be between 0 and 100.');
        }
    }

    public static function fromArray(array $options): self
    {
        return new self(
            width: (int) ($options['width'] ?? 0),
            height: (int) ($options['height'] ?? 0),
            resizingType: (string) ($options['resizing_type'] ?? 'fit'),
            enlarge: (bool) ($options['enlarge'] ?? false),
            quality: isset($options['quality']) ? (int) $options['quality'] : null,
            extension: $options['extension'] ?? null,
        );
    }
}
