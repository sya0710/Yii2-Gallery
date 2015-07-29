<?php
namespace sya\gallery;

use Yii;

class Module extends \yii\base\Module{
    
    // Duong dan thu muc upload
    public $syaDirPath;
    
    // Duong dan thu muc upload with url
    public $syaDirUrl;
    
    // Thu muc upload
    public $syaDirUpload;

    public function init() {
        parent::init();
    }
    
}