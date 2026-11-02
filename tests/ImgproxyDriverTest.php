<?php

namespace Turahe\Media\Tests;

use InvalidArgumentException;
use Turahe\Media\Drivers\ImgproxyDriver;
use Turahe\Media\Models\Media;
use Turahe\Media\Resize\ResizeOptions;

class ImgproxyDriverTest extends TestCase
{
    public function test_it_signs_urls_with_the_imgproxy_hmac()
    {
        $driver = ImgproxyDriver::fromConfig([
            'url' => 'http://imgproxy.example.com',
            'key' => '943b421c9eb07c830af81030552c86009268de4e532ba2ee2eab8247c6da0881',
            'salt' => '520f986b998545b4785e0defbc4f3c1203f22de2374a3d53cb7a7fe9fea309c5',
        ]);

        $this->assertSame(
            'http://imgproxy.example.com/m3k5QADfcKPDj-SDI2AIogZbC3FlAXszuwhtWXYqavc/rs:fit:300:300/plain/http://img.example.com/pretty/image.jpg',
            $driver->url('http://img.example.com/pretty/image.jpg', new ResizeOptions(300, 300))
        );
    }

    public function test_it_uses_an_unsafe_signature_when_key_and_salt_are_empty()
    {
        $driver = ImgproxyDriver::fromConfig([
            'url' => 'http://localhost:8080/',
            'key' => '',
            'salt' => '',
        ]);

        $url = $driver->url('http://cdn.test/a.jpg', new ResizeOptions(120, 80, 'fill', true, 75, 'webp'));

        $this->assertSame(
            'http://localhost:8080/unsafe/rs:fill:120:80:1/q:75/plain/http://cdn.test/a.jpg@webp',
            $url
        );
    }

    public function test_it_rejects_a_partial_signature_configuration()
    {
        $driver = ImgproxyDriver::fromConfig([
            'url' => 'http://localhost:8080',
            'key' => '736563726574',
            'salt' => '',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $driver->url('http://cdn.test/a.jpg', new ResizeOptions(10, 10));
    }

    public function test_media_builds_an_on_the_fly_resize_url()
    {
        config()->set('filesystems.disks.public.url', 'http://cdn.test/storage');
        config()->set('media.resize.driver', 'imgproxy');
        config()->set('media.resize.drivers.imgproxy', [
            'url' => 'http://imgproxy.test',
            'key' => '',
            'salt' => '',
        ]);
        config()->set('media.conversions.thumb', [
            'width' => 64,
            'height' => 64,
            'resizing_type' => 'fill',
        ]);

        $media = new Media;
        $media->id = 5;
        $media->file_name = 'image.jpg';
        $media->disk = 'public';

        $this->assertSame(
            'http://imgproxy.test/unsafe/rs:fill:64:64/plain/http://cdn.test/storage/5/image.jpg@jpg',
            $media->getUrl('thumb')
        );
        $this->assertSame(
            'http://cdn.test/storage/5/image.jpg',
            $media->getUrl()
        );
    }

    public function test_it_rejects_an_unknown_resize_driver()
    {
        config()->set('media.resize.driver', 'missing');

        $media = new Media;
        $media->id = 1;
        $media->file_name = 'image.jpg';
        $media->disk = 'public';

        $this->expectException(InvalidArgumentException::class);

        $media->resizeUrl(32, 32);
    }
}
