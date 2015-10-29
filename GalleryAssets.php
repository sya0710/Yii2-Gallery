<?php

namespace sya\gallery;

class GalleryAssets extends \yii\web\AssetBundle
{
	public $css = [
		'css/syagallery.css',
	];
	public $depends = [
		'sya\gallery\DropzoneAssets',
	];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets';
		parent::init();
	}
}
