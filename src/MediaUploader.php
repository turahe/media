<?php

namespace Turahe\Media;

use Illuminate\Http\UploadedFile;
use Turahe\Media\Models\Media;

class MediaUploader
{
    /** @var UploadedFile */
    protected $file;

    /** @var string */
    protected $name;

    /** @var string */
    protected $fileName;

    /** @var string */
    protected $disk;

    /** @var array */
    protected $attributes = [];

    /**
     * Create a new uploader instance.
     *
     * @return void
     */
    public function __construct(UploadedFile $file)
    {
        $this->setFile($file);
    }

    public static function fromFile(UploadedFile $file): self
    {
        return new static($file);
    }

    /**
     * Set the file to be uploaded.
     */
    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        $fileName = $file->getClientOriginalName();
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $this->setName($name);
        $this->setFileName($fileName);

        return $this;
    }

    /**
     * Set the name of the media item.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function useName(string $name): self
    {
        return $this->setName($name);
    }

    /**
     * Set the name of the file.
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $this->sanitiseFileName($fileName);

        return $this;
    }

    public function useFileName(string $fileName): self
    {
        return $this->setFileName($fileName);
    }

    /**
     * Sanitise the file name.
     */
    protected function sanitiseFileName(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    /**
     * Specify the disk where the file will be stored.
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function toDisk(string $disk): self
    {
        return $this->setDisk($disk);
    }

    /**
     * Set any custom attributes to be saved to the media item.
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function withProperties(array $properties): self
    {
        return $this->withAttributes($properties);
    }

    /**
     * Upload the file to the specified disk.
     *
     * @return mixed
     */
    public function upload()
    {
        $model = config('media.model', Media::class);

        $media = new $model;

        $media->hash = $this->file->hashName();
        $media->name = $this->name;
        $media->file_name = $this->fileName;
        $media->disk = $this->disk ?: config('media.disk');
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();

        $media->forceFill($this->attributes);

        $media->save();

        $media->filesystem()->putFileAs(
            $media->getDirectory(),
            $this->file,
            $this->fileName
        );

        return $media->fresh();
    }
}
