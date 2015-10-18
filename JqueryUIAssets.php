<?php

namespace sya\gallery;

class JqueryUIAssets extends \yii\web\AssetBundle
{
	public $sourcePath = '@vendor/bower/jquery-ui';
	public $js = [
        'jquery-ui.min.js',
	];
	public $depends = [
        'sya\gallery\DropzoneAssets',
	];
}
