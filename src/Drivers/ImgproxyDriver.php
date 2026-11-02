<?php

namespace Turahe\Media\Drivers;

use InvalidArgumentException;
use Turahe\Media\Contracts\ResizeDriver;
use Turahe\Media\Resize\ResizeOptions;

class ImgproxyDriver implements ResizeDriver
{
    public function __construct(
        protected string $url,
        protected string $key = '',
        protected string $salt = '',
    ) {}

    public static function fromConfig(array $config): self
    {
        return new self(
            url: (string) ($config['url'] ?? 'http://localhost:8080'),
            key: (string) ($config['key'] ?? ''),
            salt: (string) ($config['salt'] ?? ''),
        );
    }

    public function url(string $sourceUrl, ResizeOptions $options): string
    {
        $path = $this->path($sourceUrl, $options);

        return rtrim($this->url, '/').'/'.$this->signature($path).$path;
    }

    public function path(string $sourceUrl, ResizeOptions $options): string
    {
        $processing = sprintf(
            'rs:%s:%d:%d%s',
            $options->resizingType,
            $options->width,
            $options->height,
            $options->enlarge ? ':1' : '',
        );

        if ($options->quality !== null) {
            $processing .= '/q:'.$options->quality;
        }

        $path = '/'.$processing.'/plain/'.str_replace('@', '%40', $sourceUrl);

        if ($options->extension) {
            $path .= '@'.$options->extension;
        }

        return $path;
    }

    public function signature(string $path): string
    {
        if ($this->key === '' && $this->salt === '') {
            return 'unsafe';
        }

        if ($this->key === '' || $this->salt === '') {
            throw new InvalidArgumentException('imgproxy key and salt must both be set.');
        }

        $key = hex2bin($this->key);
        $salt = hex2bin($this->salt);

        if ($key === false || $salt === false) {
            throw new InvalidArgumentException('imgproxy key and salt must be hex-encoded.');
        }

        return rtrim(strtr(base64_encode(hash_hmac('sha256', $salt.$path, $key, true)), '+/', '-_'), '=');
    }
}
