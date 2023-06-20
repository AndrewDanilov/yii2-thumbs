Resize and cache image widget
===========
Provides widget class for resizing and caching images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require andrewdanilov/yii2-thumbs "~1.1.0"
```

or add

```
"andrewdanilov/yii2-thumbs": "~1.1.0"
```

to the `require` section of your `composer.json` file.


Usage
-----

```php
$src = \andrewdanilov\thumbs\Thumb::widget([
    'image' => '/images/img.png', // Image to resize and cache relative to base site uri
    'sizes' => '100x100', // Optional. Result image sizes. String devided with 'x' or array with 'width' and 'height' keys. Default is '100x100'
    'zc' => true, // Optional. Zoom and crop. Default is true.
    'quality' => 90, // Optional. Jpeg quality from 1 to 100. Default is 90
    'backgroundColor' => 'transparent', // Optional. Values like 'transparent', 'FFF', '000000'. Default is 'transparent'
    'noImageUri' => '/images/noimg.png', // Optional. Path to image to use for empty or absent images relative to base site uri
    'cachePath' => '/assets/thumbs/', // Optional. Path to store cached images relative to base site uri. Default is '/assets/thumbs/'
    'cacheTime' => 604800, // Optional. Time to chache in seconds. Default is 604800
]);
echo \yii\helpers\Html::img($src);
```

In the `sizes` parameter, you can specify one or both of the width and height parameters. You can describe this in string notation with the 'x' delimiter, or in array notation with the keys `width` and `height`:

```php
// width x height string notation
$sizes = '400x200';
$sizes = '400x';
$sizes = 'x200';
// array notation
$sizes = ['width' => 400, 'height' => 200];
$sizes = ['width' => 400];
$sizes = ['height' => 200];
```

If both sizes are specified, then depending on the value of the `zc` parameter, the resulting image will be cropped (`zc = true`) or padded (`zc = false`) with fields with the color specified in the `backgroundColor` parameter.

The URI in the `noImageUri` parameter must exist. If you don't have it, you can copy it from `/vendor/andrewdanilov/yii2-thumbs/src/web/images/noimage.png` to your location.

If the path from the `cachePath` parameter does not exist, it will be created.