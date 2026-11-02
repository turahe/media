<?php

namespace Turahe\Media\Resize;

use InvalidArgumentException;
use Turahe\Media\Contracts\ResizeDriver;
use Turahe\Media\Drivers\ImgproxyDriver;

class ResizeManager
{
    public function driver(?string $name = null): ResizeDriver
    {
        $name ??= (string) config('media.resize.driver', 'imgproxy');
        $config = config("media.resize.drivers.{$name}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Resize driver [{$name}] is not configured.");
        }

        return match ($name) {
            'imgproxy' => ImgproxyDriver::fromConfig($config),
            default => throw new InvalidArgumentException("Resize driver [{$name}] is not supported."),
        };
    }

    public function url(string $sourceUrl, ResizeOptions $options, ?string $driver = null): string
    {
        return $this->driver($driver)->url($sourceUrl, $options);
    }
}
