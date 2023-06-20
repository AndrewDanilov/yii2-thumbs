<?php
namespace andrewdanilov\thumbs;

use Yii;
use yii\base\Widget;
use yii\imagine\Image;
use Imagine\Image\ImageInterface;

class Thumb extends Widget
{
	public $cachedImageUriPath = '@web/assets/thumbs/';
	public $noImageUri;
	public $cacheTime = 604800;

	public $backgroundColor = 'transparent'; // 'transparent', 'FFF', '000000'
	public $quality = 90;

	public $image; // 'images/img.png'
	public $sizes = '100x100';
	public $zc = true;

	public function init()
	{
		parent::init();
		if ($this->noImageUri !== null) {
			$this->noImageUri = Yii::getAlias($this->noImageUri);
			if (!file_exists($this->noImageUri)) {
				$this->noImageUri = null;
			}
		}
		if ($this->noImageUri === null) {
			$this->noImageUri = Yii::getAlias('@andrewdanilov/thumbs/web/images/noimage.png');
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

		if (!$image_uri || !file_exists($image_path)) {
			$this->image = $this->noImageUri;
		}

		$image_ext = pathinfo($image_uri, PATHINFO_EXTENSION);

		// если не картинка, то возвращаем исходный путь
		if (!in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
			return $image_uri;
		}

		$hash = md5($image_uri . ':' . (int)$width . ':' . (int)$height . ':' . (int)$this->zc . ':' . $this->quality);

		$cached_image_uri = trim(Yii::getAlias($this->cachedImageUriPath), '/') . '/' . $width . 'x' . $height . '/' . substr($hash, 0, 1) . '/' . substr($hash, 1) . '.' . $image_ext;
		$cached_image_path = Yii::getAlias('@webroot/' . $cached_image_uri);

		if (!file_exists($cached_image_path) || filemtime($cached_image_path) + $this->cacheTime < time()) {
			if ($this->zc) {
				// crop the input image, if required
				$mode = ImageInterface::THUMBNAIL_OUTBOUND;
			} else {
				// fit the input image, if required
				$mode = ImageInterface::THUMBNAIL_INSET;
			}

			@mkdir(dirname($cached_image_path), 0777, true);
			if (!$this->backgroundColor || ($this->backgroundColor == 'transparent')) {
				Image::$thumbnailBackgroundAlpha = 0;
			} else {
				Image::$thumbnailBackgroundAlpha = 100;
				Image::$thumbnailBackgroundColor = $this->backgroundColor;
			}
			Image::thumbnail($image_path, $width, $height, $mode)
				->save($cached_image_path, ['quality' => $this->quality]);
		}

		return Yii::getAlias('@web/') . $cached_image_uri;
	}
}