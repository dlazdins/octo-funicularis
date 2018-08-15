<?php

return [

    /*
     * By default Glide uses the GD library. However you can also use Glide with ImageMagick
     * if the Imagick PHP extension is installed.
     */
    'driver' => env('GLIDE_DRIVER', 'gd'),

    /*
     * Name of the disk where source images are stored
     */
    'source_disk' => env('GLIDE_SOURCE_DISK', 'public'),

    /*
     * Name of the disk where processed images must be stored
     */
    'cache_disk' => env('GLIDE_CACHE_DISK', 'images_cache'),

    /*
     * Name of the disk where watermarks are stored
     */
    'watermarks_disk' => env('GLIDE_WATERMARKS_DISK', 'local'),

    /*
     * In certain situations you may want to define default image manipulations.
     * For example, maybe you want to specify that all images are outputted as JPEGs (fm=jpg).
     * Or maybe you have a watermark that you want added to all images.
     * Glide makes this possible using default manipulations.
     */
    'defaults' => [],

    /*
     * Glide also makes it possible to define groups of defaults, known as presets.
     * This is helpful if you have standard image manipulations that you use throughout your app.
     */
    'presets' => [
        'thumbnail_200x200' => [
            'w' => 200,
            'h' => 200,
            'fit' => 'crop',
        ],
    ],
];
