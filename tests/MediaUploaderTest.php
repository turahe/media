<?php

namespace Turahe\Media\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Turahe\Media\MediaUploader;
use Turahe\Media\Models\Media;
use Turahe\Media\Tests\Models\Media as CustomMedia;

class MediaUploaderTest extends TestCase
{
    const DEFAULT_DISK = 'default';

    protected function setUp(): void
    {
        parent::setUp();

        // Use a test disk as the default disk...
        $this->app['config']->set('media.disk', self::DEFAULT_DISK);

        // Create a test filesystem for the default disk...
        Storage::fake(self::DEFAULT_DISK);
    }

    public function test_can_upload_a_file_to_the_default_disk()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(self::DEFAULT_DISK, $media->disk);

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        $this->assertTrue($filesystem->exists($media->getPath()));
    }

    public function test_can_upload_a_file_to_a_specific_disk()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $customDisk = 'custom';

        // Create a test filesystem for the custom disk...
        Storage::fake($customDisk);

        $media = MediaUploader::fromFile($file)
            ->setDisk($customDisk)
            ->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($customDisk, $media->disk);

        $filesystem = Storage::disk($customDisk);

        $this->assertTrue($filesystem->exists($media->getPath()));
    }

    public function test_can_change_the_name_of_the_media_model()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)
            ->useName($newName = 'New name')
            ->upload();

        $this->assertEquals($newName, $media->name);
    }

    public function test_can_rename_the_file_before_it_gets_uploaded()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)
            ->useFileName($newFileName = 'new-file-name.jpg')
            ->upload();

        $this->assertEquals($newFileName, $media->file_name);
    }

    public function test_will_sanitise_the_file_name()
    {
        $file = UploadedFile::fake()->image('bad file name#023.jpg');

        $media = MediaUploader::fromFile($file)->upload();

        $this->assertEquals('bad-file-name-023.jpg', $media->file_name);
    }

    public function test_can_save_custom_attributes_to_the_media_model()
    {
        config()->set('media.model', CustomMedia::class);

        $file = UploadedFile::fake()->image('image.jpg');

        $media = MediaUploader::fromFile($file)
            ->withAttributes([
                'custom_attribute' => 'Custom attribute',
            ])
            ->upload();

        $this->assertInstanceOf(CustomMedia::class, $media);
        $this->assertEquals('Custom attribute', $media->custom_attribute);
    }

    public function test_can_upload_from_url()
    {
        // Use Laravel HTTP fake if available
        if (class_exists('Illuminate\\Support\\Facades\\Http')) {
            \Illuminate\Support\Facades\Http::fake([
                'https://example.com/test.jpg' => \Illuminate\Support\Facades\Http::response(
                    file_get_contents(__DIR__.'/Database/Factories/test-image.jpg'),
                    200,
                    ['Content-Type' => 'image/jpeg']
                ),
            ]);
        } else {
            // If not available, skip this test
            $this->markTestSkipped('Laravel HTTP fake not available.');
            return;
        }

        // Place a test image in the correct location for this test
        $url = 'https://example.com/test.jpg';
        $media = MediaUploader::fromUrl($url)->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(self::DEFAULT_DISK, $media->disk);
        $this->assertStringEndsWith('.jpg', $media->file_name);
        $filesystem = Storage::disk(self::DEFAULT_DISK);
        $this->assertTrue($filesystem->exists($media->getPath()));
    }

    public function test_can_upload_from_base64()
    {
        // Create a fake image and encode as base64
        $file = UploadedFile::fake()->image('test.png');
        $fileContent = file_get_contents($file->getRealPath());
        $base64 = base64_encode($fileContent);
        $dataUri = 'data:image/png;base64,' . $base64;

        $media = MediaUploader::fromBase64($dataUri, 'test.png')->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(self::DEFAULT_DISK, $media->disk);
        $this->assertEquals('test.png', $media->file_name);
        $filesystem = Storage::disk(self::DEFAULT_DISK);
        $this->assertTrue($filesystem->exists($media->getPath()));
    }
}
