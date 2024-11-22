<?php

namespace Turahe\Media;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Media\Models\Media;

trait HasMedia
{
    /** @var MediaGroup[] */
    protected $mediaGroups = [];

    /**
     * Get the "media" relationship.
     *
     * @return MorphToMany
     */
    public function media()
    {
        return $this
            ->morphToMany(config('media.model', Media::class), 'mediable')
            ->withPivot('group');
    }

    /**
     * Determine if there is any media in the specified group.
     *
     * @return mixed
     */
    public function hasMedia(string $group = 'default')
    {
        return $this->getMedia($group)->isNotEmpty();
    }

    /**
     * Get all the media in the specified group.
     *
     * @return mixed
     */
    public function getMedia(string $group = 'default')
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
     * @return void
     */
    public function attachMedia($media, string $group = 'default')
    {
        $this->registerMediaGroups();

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
     * Register all the model's media groups.
     *
     * @return void
     */
    public function registerMediaGroups()
    {
        //
    }

    /**
     * Register a new media group.
     *
     * @return MediaGroup
     */
    protected function addMediaGroup(string $name)
    {
        $group = new MediaGroup;

        $this->mediaGroups[$name] = $group;

        return $group;
    }

    /**
     * Get the media group with the specified name.
     *
     * @return MediaGroup|null
     */
    public function getMediaGroup(string $name)
    {
        return $this->mediaGroups[$name] ?? null;
    }

    /**
     * Detach the specified media.
     *
     * @param  mixed  $media
     * @return void
     */
    public function detachMedia($media = null)
    {
        $this->media()->detach($media);
    }

    /**
     * Detach all the media in the specified group.
     *
     * @return void
     */
    public function clearMediaGroup(string $group = 'default')
    {
        $this->media()->wherePivot('group', $group)->detach();
    }
}
