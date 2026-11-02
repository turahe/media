<?php

use Turahe\Media\Models\Media;

return [

    /*
     * The disk where files should be uploaded.
     */
    'disk' => 'public',

    /*
     * The queue used to perform image conversions.
     * Leave empty to use the default queue driver.
     */
    'queue' => null,

    'table' => 'media',

    /*
     * The fully qualified class name of the media model.
     */
    'model' => Media::class,

    /*
     * Resize images when their URL is requested instead of storing derivatives.
     */
    'resize' => [
        'driver' => env('MEDIA_RESIZE_DRIVER', 'imgproxy'),

        /*
         * Prepended to relative disk URLs so the resize service can fetch the original.
         */
        'source_url' => env('IMGPROXY_SOURCE_URL'),

        'drivers' => [
            'imgproxy' => [
                'url' => env('IMGPROXY_URL', 'http://localhost:8080'),
                'key' => env('IMGPROXY_KEY', ''),
                'salt' => env('IMGPROXY_SALT', ''),
            ],
        ],
    ],

    /*
     * Named sizes passed to the resize driver by Media::getUrl($name).
     *
     * 'thumb' => ['width' => 64, 'height' => 64, 'resizing_type' => 'fill'],
     */
    'conversions' => [],

];
