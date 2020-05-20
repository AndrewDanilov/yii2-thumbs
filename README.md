Resize and cache image widget
===========
Provides widget class for resizing and caching images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require andrewdanilov/yii2-thumbs "~1.0.0"
```

or add

```
"andrewdanilov/yii2-thumbs": "~1.0.0"
```

to the `require` section of your `composer.json` file.


Usage
-----

```php
$src = \andrewdanilov\thumbs\Thumb::widget([
    'image' => 'images/img.png', // Image to resize and cache
    'sizes' => '100x100', // Optional. Result image sizes. Default is '100x100'
    'cachedImageUriPath' => '@web/assets/thumbs/', // Optional. Path to store cached images
    'noImageUri' => '@web/images/noimage.png', // Optional. Path to image to use for empty or absent images
    'cacheTime' => 604800, // Optional. Time to chache in seconds. Default is 604800
    'backgroundColor' => 'transparent', // Optional. Values like 'transparent', 'FFF', '000000'. Default is 'transparent'
    'quality' => 90, // Optional. Jpeg quality from 1 to 100. Default is 90
    'zc' => true, // Optional. Zoom and crop. Default is true.
]);
echo \yii\helpers\Html::img($src);
```
