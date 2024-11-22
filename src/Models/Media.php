<?php

namespace Turahe\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

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
        return $this->filesystem()->url(
            $this->getPath($conversion)
        );
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
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }
}
