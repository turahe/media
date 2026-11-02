<?php

namespace Turahe\Media\Contracts;

use Turahe\Media\Resize\ResizeOptions;

interface ResizeDriver
{
    /**
     * Build a URL that resizes the source image when it is requested.
     */
    public function url(string $sourceUrl, ResizeOptions $options): string;
}
