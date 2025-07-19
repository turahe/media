<?php

namespace Turahe\Media;

use Illuminate\Http\UploadedFile;
use Turahe\Media\Models\Media;

final class MediaUploader
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
        return new self($file);
    }

    /**
     * Create a new uploader instance from a URL.
     *
     * @param string $url
     * @return self
     * @throws \Exception
     */
    public static function fromUrl(string $url): self
    {
        // Try to get the file name from the URL
        $fileName = basename(parse_url($url, PHP_URL_PATH));
        if (!$fileName) {
            throw new \Exception('Unable to determine file name from URL.');
        }

        // Use Guzzle to download the file content
        try {
            if (class_exists('Illuminate\\Support\\Facades\\Http')) {
                // Laravel HTTP client (wrapper for Guzzle)
                $response = \Illuminate\Support\Facades\Http::get($url);
                if (!$response->successful()) {
                    throw new \Exception('Failed to download file from URL.');
                }
                $fileContent = $response->body();
            } else {
                // Direct Guzzle usage
                $client = new \GuzzleHttp\Client();
                $response = $client->get($url);
                if ($response->getStatusCode() >= 400) {
                    throw new \Exception('Failed to download file from URL.');
                }
                $fileContent = $response->getBody()->getContents();
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to download file from URL: ' . $e->getMessage());
        }

        // Store the file temporarily
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'media_url_');
        file_put_contents($tmpFilePath, $fileContent);

        // Guess the mime type
        $mimeType = mime_content_type($tmpFilePath) ?: 'application/octet-stream';

        // Create UploadedFile instance
        $uploadedFile = new UploadedFile(
            $tmpFilePath,
            $fileName,
            $mimeType,
            null,
            true // Mark as test mode (local file)
        );

        return new self($uploadedFile);
    }

    /**
     * Create a new uploader instance from a base64-encoded string.
     *
     * @param string $base64
     * @param string|null $fileName
     * @return self
     * @throws \Exception
     */
    public static function fromBase64(string $base64, string $fileName = null): self
    {
        // Extract base64 data if data URI scheme is used
        if (preg_match('/^data:(.*?);base64,(.*)$/', $base64, $matches)) {
            $base64 = $matches[2];
        }

        // Decode base64
        $fileContent = base64_decode($base64);
        if ($fileContent === false) {
            throw new \Exception('Failed to decode base64 string.');
        }

        // Store the file temporarily
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'media_b64_');
        file_put_contents($tmpFilePath, $fileContent);

        // Guess the mime type
        $mimeType = mime_content_type($tmpFilePath) ?: 'application/octet-stream';

        // Generate file name if not provided
        if (!$fileName) {
            $extension = explode('/', $mimeType)[1] ?? 'bin';
            $fileName = uniqid('media_', true) . '.' . $extension;
        }

        // Create UploadedFile instance
        $uploadedFile = new UploadedFile(
            $tmpFilePath,
            $fileName,
            $mimeType,
            null,
            true // Mark as test mode (local file)
        );

        return new self($uploadedFile);
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
