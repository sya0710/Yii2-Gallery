<?php

namespace sya\gallery;

class DropzoneAssets extends \yii\web\AssetBundle
{
	public $sourcePath = '@vendor/bower/dropzone/dist/min';
	public $js = [
        'dropzone.min.js',
	];
	public $css = [
		'basic.min.css',
		'dropzone.min.css'
	];
	public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
	];
}
