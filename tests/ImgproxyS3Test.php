<?php

namespace Turahe\Media\Tests;

use Aws\S3\Exception\S3Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Turahe\Media\MediaUploader;
use Turahe\Media\Models\Media;

class ImgproxyS3Test extends TestCase
{
    public function test_s3_disk_is_passed_to_imgproxy_as_an_s3_url()
    {
        config()->set('filesystems.disks.s3', [
            'driver' => 's3',
            'bucket' => 'media',
        ]);
        config()->set('media.resize.driver', 'imgproxy');
        config()->set('media.resize.drivers.imgproxy', [
            'url' => 'http://imgproxy.test',
            'key' => '',
            'salt' => '',
        ]);

        $media = new Media;
        $media->id = 7;
        $media->file_name = 'photo.png';
        $media->disk = 's3';

        $this->assertSame(
            'http://imgproxy.test/unsafe/rs:fit:40:30/plain/s3://media/7/photo.png@png',
            $media->resizeUrl(40, 30)
        );
    }

    public function test_imgproxy_resizes_an_image_stored_in_rustfs()
    {
        if (! getenv('AWS_ENDPOINT') || ! getenv('IMGPROXY_URL')) {
            $this->markTestSkipped('Set AWS_ENDPOINT and IMGPROXY_URL to test imgproxy against RustFS.');
        }

        $bucket = getenv('AWS_BUCKET') ?: 'media';

        config()->set('media.disk', 's3');
        config()->set('media.resize.driver', 'imgproxy');
        config()->set('media.resize.drivers.imgproxy', [
            'url' => getenv('IMGPROXY_URL'),
            'key' => getenv('IMGPROXY_KEY') ?: '',
            'salt' => getenv('IMGPROXY_SALT') ?: '',
        ]);
        config()->set('filesystems.disks.s3', [
            'driver' => 's3',
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'bucket' => $bucket,
            'endpoint' => getenv('AWS_ENDPOINT'),
            'use_path_style_endpoint' => filter_var(getenv('AWS_USE_PATH_STYLE_ENDPOINT') ?: 'true', FILTER_VALIDATE_BOOL),
            'throw' => true,
        ]);

        $disk = Storage::disk('s3');

        try {
            $disk->getClient()->createBucket(['Bucket' => $bucket]);
        } catch (S3Exception $exception) {
            if (! in_array($exception->getAwsErrorCode(), ['BucketAlreadyOwnedByYou', 'BucketAlreadyExists'], true)) {
                throw $exception;
            }
        }

        $media = MediaUploader::fromFile(UploadedFile::fake()->image('photo.png', 80, 60))
            ->useFileName('photo.png')
            ->upload();

        $this->assertTrue($disk->exists($media->getPath()));

        $url = $media->resizeUrl(32, 32);

        $this->assertStringContainsString('s3://'.$bucket.'/', $url);

        $response = Http::timeout(20)->get($url);

        $this->assertTrue($response->successful(), $response->body());
        $this->assertStringStartsWith('image/', (string) $response->header('Content-Type'));

        $image = imagecreatefromstring($response->body());

        $this->assertNotFalse($image);
        $this->assertLessThanOrEqual(32, imagesx($image));
        $this->assertLessThanOrEqual(32, imagesy($image));
    }
}
