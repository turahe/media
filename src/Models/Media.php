<?php

namespace Turahe\Media\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Turahe\Media\Resize\ResizeManager;
use Turahe\Media\Resize\ResizeOptions;

class Media extends Model implements Sortable
{
    use NodeTrait;
    use SoftDeletes;
    use SortableTrait;

    protected $guarded = [];

    public function getLftName()
    {
        return 'record_left';
    }

    public function getRgtName()
    {
        return 'record_right';
    }

    public function getParentIdName()
    {
        return 'parent_id';
    }

    /**
     * Specify parent id attribute mutator
     *
     * @throws \Exception
     */
    public function setParentAttribute($value)
    {
        $this->setParentIdAttribute($value);
    }

    public $sortable = [
        'order_column_name' => 'record_ordering',
        'sort_when_creating' => true,
    ];

    public function __construct(array $attributes = [])
    {

        parent::__construct($attributes);

        $this->guarded[] = $this->primaryKey;
        $this->table = config('media.table') ?: parent::getTable();
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type.
     */
    public function getTypeAttribute(): ?string
    {
        return Str::before($this->mime_type, '/') ?? null;
    }

    /**
     * Determine if the file is of the specified type.
     */
    public function isOfType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get the url to the file.
     *
     * @return mixed
     */
    public function getUrl(string $conversion = '')
    {
        $conversionOptions = $this->onTheFlyConversion($conversion);

        if ($conversionOptions !== null) {
            return $this->resizeUrl(
                (int) ($conversionOptions['width'] ?? 0),
                (int) ($conversionOptions['height'] ?? 0),
                $conversionOptions,
            );
        }

        return $this->filesystem()->url(
            $this->getPath($conversion)
        );
    }

    /**
     * URL of this file resized on the fly by the configured driver.
     */
    public function resizeUrl(int $width, int $height = 0, array $options = []): string
    {
        $options['width'] = $options['width'] ?? $width;
        $options['height'] = $options['height'] ?? $height;

        if (! array_key_exists('extension', $options) && $this->extension) {
            $options['extension'] = $this->extension;
        }

        return app(ResizeManager::class)->url(
            $this->resizeSourceUrl(),
            ResizeOptions::fromArray($options),
        );
    }

    /**
     * Absolute URL imgproxy (or another driver) can fetch.
     * S3 disks use the s3:// scheme so imgproxy reads the object with its own credentials.
     */
    public function resizeSourceUrl(): string
    {
        $disk = config('filesystems.disks.'.$this->disk);

        if (is_array($disk) && ($disk['driver'] ?? null) === 's3' && ! empty($disk['bucket'])) {
            return 's3://'.$disk['bucket'].'/'.ltrim($this->getPath(), '/');
        }

        $url = $this->filesystem()->url($this->getPath());

        if (preg_match('#^https?://#i', (string) $url) === 1) {
            return $url;
        }

        $base = rtrim((string) config('media.resize.source_url', ''), '/');

        if ($base === '') {
            return $url;
        }

        return $base.'/'.ltrim((string) $url, '/');
    }

    protected function onTheFlyConversion(string $conversion): ?array
    {
        if ($conversion === '') {
            return null;
        }

        $options = config('media.conversions.'.$conversion);

        return is_array($options) ? $options : null;
    }

    /**
     * Get the full path to the file.
     *
     * @return mixed
     */
    public function getFullPath(string $conversion = '')
    {
        return $this->filesystem()->path(
            $this->getPath($conversion)
        );
    }

    /**
     * Get the path to the file on disk.
     */
    public function getPath(string $conversion = ''): string
    {
        $directory = $this->getDirectory();

        if ($conversion) {
            $directory .= '/conversions/'.$conversion;
        }

        return $directory.'/'.$this->file_name;
    }

    /**
     * Get the directory for files on disk.
     *
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->getKey();
    }

    /**
     * Get the filesystem where the associated file is stored.
     *
     * @return Filesystem
     */
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }
}
