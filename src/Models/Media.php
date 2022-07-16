<?php

namespace Turahe\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;


/**
 * Turahe\Media\Models\Media
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $file_name
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property int|null $record_left
 * @property int|null $record_right
 * @property int|null $record_dept
 * @property int|null $record_ordering
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection|Media[] $children
 * @property-read int|null $children_count
 * @property-read string $extension
 * @property-read string|null $type
 * @property Media|null $parent
 * @method static \Kalnoy\Nestedset\Collection|static[] all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media ancestorsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media ancestorsOf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media applyNestedSetScope(?string $table = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media countErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media d()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media defaultOrder(string $dir = 'asc')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media descendantsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media descendantsOf($id, array $columns = [], $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media fixSubtree($root)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media fixTree($root = null)
 * @method static \Kalnoy\Nestedset\Collection|static[] get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getPlainNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getTotalErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media hasChildren()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media hasParent()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media isBroken()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media leaves(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media makeGap(int $cut, int $height)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media moveNode($key, $position)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media newModelQuery()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media newQuery()
 * @method static \Illuminate\Database\Query\Builder|Media onlyTrashed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereAncestorOf(bool $id, bool $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereNodeBetween($values)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media query()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media rebuildSubtree($root, array $data, $delete = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media rebuildTree(array $data, $delete = false, $root = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media reversed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media root(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereAncestorOf($id, $andSelf = false, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereAncestorOrSelf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereCreatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDeletedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDescendantOf($id, $boolean = 'and', $not = false, $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDescendantOrSelf(string $id, string $boolean = 'and', string $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDisk($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereFileName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsAfter($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsBefore($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsLeaf()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsRoot()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereMimeType($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereNodeBetween($values, $boolean = 'and', $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereParentId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordDept($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordLeft($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordOrdering($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordRight($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereSize($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereUpdatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereUuid($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media withDepth(string $as = 'depth')
 * @method static \Illuminate\Database\Query\Builder|Media withTrashed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media withoutRoot()
 * @method static \Illuminate\Database\Query\Builder|Media withoutTrashed()
 * @mixin \Eloquent
 */

class Media extends Model
{
    use NodeTrait;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size',
    ];

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

    /**
     * Bootstrap the model and its traits.
     *
     * Caching model when updating and
     * delete cache when delete models
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        static::creating(function ($instance) {
            $instance->uuid = Str::uuid()->toString();
        });
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type.
     *
     * @return string|null
     */
    public function getTypeAttribute(): ?string
    {
        return Str::before($this->mime_type, '/') ?? null;
    }

    /**
     * Determine if the file is of the specified type.
     *
     * @param string $type
     * @return bool
     */
    public function isOfType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get the url to the file.
     *
     * @param string $conversion
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
     * @param string $conversion
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
     *
     * @param string $conversion
     * @return string
     */
    public function getPath(string $conversion = '')
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
