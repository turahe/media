<?php

namespace Turahe\Media;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Turahe\Media\Models\Media;

interface HasMediaInterface
{
    public function media(): MorphToMany;

    public function hasMedia(string $group = 'default'): bool;

    public function getMedia(string $group = 'default'): Collection;

    public function getFirstMedia(string $group = 'default'): ?Media;

    public function clearMediaGroup(string $group = 'default'): bool;

    public function detachMedia($media = null): bool;

    public function getFirstMediaUrl(string $group = 'default', string $conversion = ''): string;

    public function attachMedia($media, string $group = 'default');
}
