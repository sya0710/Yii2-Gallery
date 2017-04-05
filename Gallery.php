<?php
namespace sya\gallery;

use Yii;

class Gallery extends \sya\gallery\widgets\BaseWidget {
    
	private static $_instance = null;

    /**
     * @return \sya\gallery\widgets\BaseWidget
     */
    public static function getInstance()
    {
    	if (!is_null(self::$_instance))
            return self::$_instance;
        else {
            $className = __CLASS__;
            $widget = self::$_instance = new $className();
            return $widget;
        }
    }

}