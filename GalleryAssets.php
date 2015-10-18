<?php

namespace sya\gallery;

class GalleryAssets extends \yii\web\AssetBundle
{
	public $sourcePath = '@vendor/sya/yii2-gallery/assets';
	public $css = [
        'css/syagallery.css',
	];
	public $depends = [
        'sya\gallery\JqueryUIAssets',
	];
}
