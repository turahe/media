<?php

namespace Turahe\Media\Tests;

use Illuminate\Filesystem\Filesystem;
use Mockery;
use Turahe\Media\Models\Media;

class MediaTest extends TestCase
{
    public function test_has_an_extension_accessor()
    {
        $image = new Media;
        $image->file_name = 'image.png';

        $video = new Media;
        $video->file_name = 'video.mov';

        $this->assertEquals('png', $image->extension);
        $this->assertEquals('mov', $video->extension);
    }

    public function test_has_a_type_accessor()
    {
        $image = new Media;
        $image->mime_type = 'image/png';

        $video = new Media;
        $video->mime_type = 'video/mov';

        $this->assertEquals('image', $image->type);
        $this->assertEquals('video', $video->type);
    }

    public function test_can_determine_its_type()
    {
        $media = new Media;
        $media->mime_type = 'image/png';

        $this->assertTrue($media->isOfType('image'));
        $this->assertFalse($media->isOfType('video'));
    }

    public function test_can_get_the_path_on_disk_to_the_file()
    {
        $media = new Media;
        $media->id = 1;
        $media->file_name = 'image.jpg';

        $this->assertEquals('1/image.jpg', $media->getPath());
    }

    public function test_can_get_the_path_on_disk_to_a_converted_image()
    {
        $media = new Media;
        $media->id = 1;
        $media->file_name = 'image.jpg';

        $this->assertEquals(
            '1/conversions/thumbnail/image.jpg',
            $media->getPath('thumbnail')
        );
    }

    public function test_can_get_the_full_path_to_the_file()
    {
        $media = Mockery::mock(Media::class)->makePartial();

        $filesystem = Mockery::mock(Filesystem::class)->makePartial();

        // Assert filesystem calls path with the correct path on disk...
        $filesystem->shouldReceive('path')->with($media->getPath())->once()->andReturn('path');

        $media->shouldReceive('filesystem')->once()->andReturn($filesystem);

        $this->assertEquals('path', $media->getFullPath());
    }

    public function test_can_get_the_full_path_to_a_converted_image()
    {
        $media = Mockery::mock(Media::class)->makePartial();

        $filesystem = Mockery::mock(Filesystem::class)->makePartial();

        // Assert filesystem calls path with the correct path on disk...
        $filesystem->shouldReceive('path')->with($media->getPath('thumbnail'))->once()->andReturn('path');

        $media->shouldReceive('filesystem')->once()->andReturn($filesystem);

        $this->assertEquals('path', $media->getFullPath('thumbnail'));
    }

    public function test_can_get_the_url_to_the_file()
    {
        $media = Mockery::mock(Media::class)->makePartial();

        $filesystem = Mockery::mock(Filesystem::class)->makePartial();

        // Assert filesystem calls url with the correct path on disk...
        $filesystem->shouldReceive('url')->with($media->getPath())->once()->andReturn('url');

        $media->shouldReceive('filesystem')->once()->andReturn($filesystem);

        $this->assertEquals('url', $media->getUrl());
    }

    public function test_can_get_the_url_to_a_converted_image()
    {
        $media = Mockery::mock(Media::class)->makePartial();

        $filesystem = Mockery::mock(Filesystem::class)->makePartial();

        // Assert filesystem calls url with the correct path on disk...
        $filesystem->shouldReceive('url')->with($media->getPath('thumbnail'))->once()->andReturn('url');

        $media->shouldReceive('filesystem')->once()->andReturn($filesystem);

        $this->assertEquals('url', $media->getUrl('thumbnail'));
    }
}
