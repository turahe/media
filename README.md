# Laravel Media

[![Tests](https://github.com/turahe/media/actions/workflows/run-tests.yml/badge.svg)](https://github.com/turahe/media/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/turahe/media?style=flat-square)](https://codecov.io/gh/turahe/media)
[![PHP Version](https://img.shields.io/packagist/php-v/turahe/media?style=flat-square)](https://packagist.org/packages/turahe/media)
[![StyleCI](https://github.styleci.io/repos/185000000/shield?branch=master)](https://github.styleci.io/repos/185000000)
[![Latest Stable Version](https://img.shields.io/packagist/v/turahe/media.svg?style=flat-square)](https://packagist.org/packages/turahe/media)
[![Total Downloads](https://img.shields.io/packagist/dt/turahe/media.svg?style=flat-square)](https://packagist.org/packages/turahe/media)
[![License](https://img.shields.io/github/license/turahe/media.svg?style=flat-square)](LICENSE)

An easy solution to attach files to your Eloquent models, with image manipulation built in!

---

## Table of Contents

- [Installation](#installation)
- [Key Concepts](#key-concepts)
- [Uploading Media](#uploading-media)
  - [From Uploaded File](#from-uploaded-file)
  - [From URL](#from-url)
  - [From Base64 String](#from-base64-string)
  - [Customizing Uploads](#customizing-uploads)
- [Associating Media with Models](#associating-media-with-models)
- [Disassociating & Deleting Media](#disassociating--deleting-media)
- [Retrieving Media](#retrieving-media)
- [Image Manipulation & Conversions](#image-manipulation--conversions)

---

## Installation

Install the package via Composer:

```bash
composer require turahe/media
```

Publish the assets to create the necessary migration and config files:

```bash
php artisan vendor:publish --provider="Turahe\Media\MediaServiceProvider"
```

---

## Key Concepts

- **Media** can be any file type (image, document, etc). Restrict file types in your own validation logic.
- **Media** is uploaded as its own entity and can be managed independently.
- **Associations**: Media must be attached to a model to be related.
- **Groups**: Media items are bound to "groups" (e.g., images, documents) for flexible associations.
- **Conversions**: You can define image conversions (e.g., thumbnails) to be performed when media is attached.
- **Global Conversions**: Conversions are registered globally and reusable across models.

---

## Uploading Media

Use the `Turahe\Media\MediaUploader` class to handle file uploads. By default, files are saved to the disk specified in your media config, with a sanitized file name, and a media record is created in the database.

### From Uploaded File

```php
$file = $request->file('file');

// Default usage
$media = MediaUploader::fromFile($file)->upload();
```

### Customizing Uploads

You can customize the file name and media name before uploading:

```php
$media = MediaUploader::fromFile($file)
    ->useFileName('custom-file-name.jpeg')
    ->useName('Custom media name')
    ->upload();
```

### From URL

Upload media directly from a remote URL:

```php
$media = MediaUploader::fromUrl('https://example.com/image.jpg')->upload();
```

### From Base64 String

Upload media from a base64-encoded string (with or without a data URI prefix):

```php
$base64 = '...'; // your base64 string
$media = MediaUploader::fromBase64($base64, 'image.png')->upload();
```

---

## Associating Media with Models

To associate media with a model, include the `Turahe\Media\HasMedia` trait:

```php
class Post extends Model
{
    use HasMedia;
}
```

Attach media to a model:

```php
$post = Post::first();

// To the default group
$post->attachMedia($media);

// To a custom group
$post->attachMedia($media, 'custom-group');
```

---

## Disassociating & Deleting Media

Detach media from a model:

```php
// Detach all media
$post->detachMedia();

// Detach specific media
$post->detachMedia($media);

// Detach all media in a group
$post->clearMediaGroup('your-group');
```

Delete a media item (removes file and associations):

```php
Media::first()->delete();
```

---

## Retrieving Media

Retrieve media attached to a model:

```php
// All media in the default group
$post->getMedia();

// All media in a custom group
$post->getMedia('custom-group');

// First media item in the default group 
$post->getFirstMedia();

// First media item in a custom group
$post->getFirstMedia('custom-group');
```

Get URLs for media:

```php
// URL of the first media item in the default group
$post->getFirstMediaUrl();

// URL of the first media item in a custom group
$post->getFirstMediaUrl('custom-group');
```

---

## Image Manipulation & Conversions

You can define image conversions (e.g., thumbnails) using the familiar `intervention/image` library. Register conversions in a service provider:

```php
use Intervention\Image\Image;
use Turahe\Media\Facades\Conversion;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Conversion::register('thumb', function (Image $image) {
            return $image->fit(64, 64);
        });
    }
}
```

Configure a media group to perform conversions:

```php
class Post extends Model
{
    use HasMedia;
    
    public function registerMediaGroups()
    {
        $this->addMediaGroup('gallery')
             ->performConversions('thumb');
    }
}
```

Get the URL of a converted image:

```php
// The thumbnail of the first image in the gallery group
$post->getFirstMediaUrl('gallery', 'thumb');
```
