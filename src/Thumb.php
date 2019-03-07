<?php

namespace andrewdanilov\Thumbs;

use Yii;
use yii\imagine\Image;
use Imagine\Image\ImageInterface;

class Thumb
{
	public static $cachedImageUriPath = '@web/assets/thumbs/';
	public static $noImageUri = '@web/assets/thumbs/noimage.png';
	public static $cacheTime = 604800;

	/**
	 * Возвращает uri к закэшированному варианту файла.
	 * Если файла в кэше нет, то создает его.
	 * Если нет исходного файла, то создает кэш дефолтного изображения.
	 *
	 * Пример: Yii::$app->thumbs->url('images/img.png', '120x100', false, 70, 'FFF')
	 *
	 * @param string $image_uri
	 * @param string|array $sizes
	 * @param bool $zc - crop on zoom
	 * @param int $quality
	 * @param string $backgroundColor
	 * @return string
	 */
	public static function url($image_uri, $sizes, $zc=true, $quality=90, $backgroundColor='transparent')
	{
		if (is_array($sizes)) {
			$width = array_key_exists('width', $sizes) ? $sizes['width'] : 0;
			$height = array_key_exists('height', $sizes) ? $sizes['height'] : 0;
		} else {
			list($width, $height) = array_merge(explode('x', $sizes), ['']);
		}
		$width = (int)$width ?: null;
		$height = (int)$height ?: null;
		if (!$width && !$height) {
			return '';
		}

		// zoomcrop не имеет смысла, если не указаны размеры обеих сторон
		if (!$width || !$height) {
			$zc = false;
		}

		if (!$image_uri) {
			$image_uri = Yii::getAlias(self::$noImageUri);
		}

		$image_uri = urldecode(trim($image_uri, '/'));
		$image_path = Yii::getAlias('@webroot/' . $image_uri);

		if (!file_exists($image_path)) {
			$image_uri = Yii::getAlias(self::$noImageUri);
			$image_uri = trim($image_uri, '/');
			$image_path = Yii::getAlias('@webroot/' . $image_uri);
		}

		$image_ext = pathinfo($image_uri, PATHINFO_EXTENSION);

		// если не картинка, то возвращаем исходный путь
		if (!in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif'])) {
			return $image_uri;
		}

		$hash = md5($image_uri . ':' . (int)$width . ':' . (int)$height . ':' . (int)$zc . ':' . $quality);

		$cached_image_uri = trim(Yii::getAlias(self::$cachedImageUriPath), '/') . '/' . $width . 'x' . $height . '/' . substr($hash, 0, 1) . '/' . substr($hash, 1) . '.' . $image_ext;
		$cached_image_path = Yii::getAlias('@webroot/' . $cached_image_uri);

		if (!file_exists($cached_image_path) || filemtime($cached_image_path) + self::$cacheTime < time()) {
			if ($zc) {
				// crop the input image, if required
				$mode = ImageInterface::THUMBNAIL_OUTBOUND;
			} else {
				// fit the input image, if required
				$mode = ImageInterface::THUMBNAIL_INSET;
			}

			@mkdir(dirname($cached_image_path), 0777, true);
			if (!$backgroundColor || ($backgroundColor == 'transparent')) {
				Image::$thumbnailBackgroundAlpha = 0;
			} else {
				Image::$thumbnailBackgroundAlpha = 100;
				Image::$thumbnailBackgroundColor = $backgroundColor;
			}
			Image::thumbnail($image_path, $width, $height, $mode)
				->save($cached_image_path, ['quality' => $quality]);
		}

		return $cached_image_uri;
	}
}