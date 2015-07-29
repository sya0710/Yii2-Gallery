<?php

namespace sya\gallery;

class GalleryAssets extends \yii\web\AssetBundle
{
	public $sourcePath = '@vendor/bower/jquery-ui';
	public $js = [
        'jquery-ui.min.js',
	];
	public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
	];
}
