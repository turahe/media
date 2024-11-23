<?php

namespace Turahe\Media;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Media\Models\Media;

trait HasMedia
{
    /**
     * Get the "media" relationship.
     */
    public function media(): MorphToMany
    {
        return $this
            ->morphToMany(config('media.model', Media::class), 'mediable')
            ->withPivot('group');
    }

    /**
     * Determine if there is any media in the specified group.
     */
    public function hasMedia(string $group = 'default'): bool
    {
        return $this->getMedia($group)->isNotEmpty();
    }

    /**
     * Get all the media in the specified group.
     */
    public function getMedia(string $group = 'default'): Collection
    {
        return $this->media->where('pivot.group', $group);
    }

    /**
     * Get the first media item in the specified group.
     *
     * @return mixed
     */
    public function getFirstMedia(string $group = 'default')
    {
        return $this->getMedia($group)->first();
    }

    /**
     * Get the url of the first media item in the specified group.
     *
     * @return string
     */
    public function getFirstMediaUrl(string $group = 'default', string $conversion = '')
    {
        if (! $media = $this->getFirstMedia($group)) {
            return '';
        }

        return $media->getUrl($conversion);
    }

    /**
     * Attach media to the specified group.
     *
     * @param  mixed  $media
     */
    public function attachMedia($media, string $group = 'default'): void
    {
        $ids = $this->parseMediaIds($media);

        $this->media()->attach($ids, [
            'group' => $group,
        ]);
    }

    /**
     * Parse the media id's from the mixed input.
     *
     * @param  mixed  $media
     */
    protected function parseMediaIds($media): array
    {
        if ($media instanceof Collection) {
            return $media->modelKeys();
        }

        if ($media instanceof Media) {
            return [$media->getKey()];
        }

        return (array) $media;
    }

    /**
     * Detach the specified media.
     *
     * @param  mixed  $media
     */
    public function detachMedia($media = null): bool
    {
        return $this->media()->detach($media);
    }

    /**
     * Detach all the media in the specified group.
     */
    public function clearMediaGroup(string $group = 'default'): bool
    {
        return $this->media()->wherePivot('group', $group)->detach();
    }
}
