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

	/**
	 * Возвращает uri к закэшированному варианту файла.
	 * Если файла в кэше нет, то создает его.
	 * Если нет исходного файла, то создает кэш дефолтного изображения.
	 *
	 * @inheritdoc
	 */
	public function run()
	{
		ThumbAsset::register($this->getView());
		$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@andrewdanilov/thumbs');

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

		if ($this->noImageUri == null) {
			$this->noImageUri = $directoryAsset . '/images/noimage.png';
		}

		if (!$this->image) {
			$this->image = Yii::getAlias($this->noImageUri);
		}

		$image_uri = urldecode(trim($this->image, '/'));
		$image_path = Yii::getAlias('@webroot/' . $image_uri);

		if (!file_exists($image_path)) {
			$image_uri = Yii::getAlias($this->noImageUri);
			$image_uri = trim($image_uri, '/');
			$image_path = Yii::getAlias('@webroot/' . $image_uri);
		}

		$image_ext = pathinfo($image_uri, PATHINFO_EXTENSION);

		// если не картинка, то возвращаем исходный путь
		if (!in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif'])) {
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

		return $cached_image_uri;
	}
}