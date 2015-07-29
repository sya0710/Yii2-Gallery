<?php
namespace sya\gallery\helpers;

use Yii;

class FileHelper extends \yii\helpers\FileHelper {
    
    const URL = 'url';
    
    const PATH = 'path';
    
    const IMAGE = 'image';

    public static function getExtention($file) {
        if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '')
            return strtolower($ext);
        return FALSE;
    }
    
    /**
     * Get url or path file uploaded
     * @param type $file
     * @param type $type URL | PATH
     * @return type
     */
    public static function getFileUploaded ($file, $type = self::URL) {
        if ($type == self::PATH)
            return Yii::getAlias(Yii::$app->getModule('gallery')->syaDirPath) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $file;
        elseif ($type == self::URL) {
            return Yii::getAlias(Yii::$app->getModule('gallery')->syaDirUrl) . Yii::$app->getModule('gallery')->syaDirUpload . DIRECTORY_SEPARATOR . $file;
        }
        return FALSE;
    }
}
