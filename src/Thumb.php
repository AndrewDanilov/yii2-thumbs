<?php
namespace andrewdanilov\thumbs;

use Yii;
use yii\base\Widget;
use yii\imagine\Image;
use Imagine\Image\ImageInterface;

class Thumb extends Widget
{
	public $image; // '/images/img.png'
	public $sizes = '100x100'; // '100x100', '400x', 'x200', ['width' => '100', 'height' => '100'], ['width' => '400'], ['height' => '200']
	public $zc = true;
	public $quality = 90; // 1..100
	public $backgroundColor = 'transparent'; // 'transparent', 'FFF', '000000'

	public $noImageUri; // '/images/noimg.png'
	public $cachePath; // '/assets/thumbs/'
	public $cacheTime; // 604800

	public function init()
	{
		parent::init();
		if ($this->noImageUri !== null) {
            $uri = urldecode(trim($this->noImageUri, '/'));
			$this->noImageUri = Yii::getAlias('@web/' . $uri);
		}
        if (!$this->cachePath) {
            $this->cachePath = '/assets/thumbs/';
        }
        if (!$this->cacheTime) {
            $this->cacheTime = 604800;
        }
	}

	/**
	 * Returns uri to cached file.
	 * If there is no cached version, method will create it.
	 * If source file doesn't exist, method creates cached
	 * version of default 'noImage' file and returns uri to it.
	 *
	 * @inheritdoc
	 */
	public function run()
	{
		if (is_array($this->sizes)) {
			$width = array_key_exists('width', $this->sizes) ? $this->sizes['width'] : 0;
			$height = array_key_exists('height', $this->sizes) ? $this->sizes['height'] : 0;
		} else {
			list($width, $height) = array_merge(explode('x', $this->sizes), ['']);
		}
		$width = (int)$width ?: null;
		$height = (int)$height ?: null;
		if (!$width && !$height) {
			return '';
		}

		// zoomcrop не имеет смысла, если не указаны размеры обеих сторон
		if (!$width || !$height) {
			$this->zc = false;
		}

		$image_uri = urldecode(trim($this->image, '/'));
		$image_path = Yii::getAlias('@webroot/' . $image_uri);

        // если исходного файла нет, то возвращаем заглушку
		if (!is_file($image_path)) {
            return $this->noImageUri;
        }

        $image_uri = Yii::getAlias('@web/' . $image_uri);

		$image_ext = pathinfo($image_uri, PATHINFO_EXTENSION);

        // если не картинка, то возвращаем заглушку
		if (!in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
			return $this->noImageUri;
		}

		$hash = md5($image_uri . ':' . (int)$width . ':' . (int)$height . ':' . (int)$this->zc . ':' . $this->quality);
		$hashed_image_pathname = $width . 'x' . $height . '/' . substr($hash, 0, 1) . '/' . substr($hash, 1) . '.' . $image_ext;

        $cache_uri = urldecode(trim($this->cachePath, '/')) . '/' . $hashed_image_pathname;
		$cache_path = Yii::getAlias('@webroot/' . $cache_uri);

		if (!file_exists($cache_path) || filemtime($cache_path) + $this->cacheTime < time()) {
			if ($this->zc) {
				// crop the input image, if required
				$mode = ImageInterface::THUMBNAIL_OUTBOUND;
			} else {
				// fit the input image, if required
				$mode = ImageInterface::THUMBNAIL_INSET;
			}

			@mkdir(dirname($cache_path), 0777, true);
			if (!$this->backgroundColor || ($this->backgroundColor == 'transparent')) {
				Image::$thumbnailBackgroundAlpha = 0;
			} else {
				Image::$thumbnailBackgroundAlpha = 100;
				Image::$thumbnailBackgroundColor = $this->backgroundColor;
			}
			Image::thumbnail($image_path, $width, $height, $mode)
				->save($cache_path, ['quality' => $this->quality]);
		}

		return Yii::getAlias('@web/') . $cache_uri;
	}
}